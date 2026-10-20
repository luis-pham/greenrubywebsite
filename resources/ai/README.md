# AI writing resources

Context files loaded by `App\Services\Ai\ArticleWriter` into the cached system prompt.

| File | Used for | Notes |
|---|---|---|
| `brand-facts.md` | All articles | Owner-confirmed facts. Single source of truth; never contradict. |
| `style-guide.md` | All articles | English voice, SEO, links, placeholders, HTML. |
| `link-policy.txt` | All articles | Competitor/OTA blocklist for search and outbound links. |
| `style-guide-vi.md` | Vietnamese only | Native Vietnamese editorial rules. Overrides the English voice rules for VI. |
| `vi-terminology.md` | Vietnamese only | Travel terminology and place-name spelling. |
| `tests/vi-fixtures.json` | Regression | Rubric fixtures for the Vietnamese pass. |

## Vietnamese pipeline

```
research (EN) → write (VI, with VI system block) → fact/SEO review (keeps VI wording)
  → VietnameseEditor::review (native editorial pass, up to 2 rounds) → link check → save
```

Why it is built this way (audit, 2026-10-21):

1. **Translationese came from the inputs.** Research notes, brand facts and product data are English, and the writer was given English voice rules ("short, opinionated, X wins on Y"). The model translated those patterns literally: "mạnh ở", "thắng về", "tàu chị em", "Nói gọn:". Fix: a Vietnamese system block that says to take facts from English sources but compose in Vietnamese, with VI-specific voice, tone-by-article-type and SEO rules.
2. **Product names were invented.** Only English product data was available. Fix: `VietnameseEditor::officialNames()` injects the official VI names of cruises, itineraries, activities and services from the CMS.
3. **No native editing step.** The single review pass focused on facts, links and SEO. Fix: a separate editorial pass that reads the whole article as a Vietnamese editor.
4. **Exact-match keyword placement forced awkward sentences.** Fix: VI SEO rules allow variants and word-order changes.

### The editorial pass (`app/Services/Ai/VietnameseEditor.php`)

The model returns surgical edits, not a rewritten article. Each edit has:
`field`, `original` (exact substring), `category`, `why`, `revision`, `meaning_changed`.
It also returns `kept` (sentences that are already natural), an acceptance checklist, 1–5 scores on ten dimensions and a pass/fail verdict.

Code, not the model, decides what gets applied. An edit is applied only when:
- `meaning_changed` is false;
- the numbers in original and revision are identical;
- hrefs and `[IMAGE…]`, `[VIDEO…]`, `[CẦN XÁC NHẬN…]` placeholders are identical (anchor text may change);
- `original` occurs exactly once in that field.

Everything else is stored as `flagged` or `unapplied` in `ai_meta.vi_review` for the human editor. This is what keeps the pass from changing facts, service status (current / planned / design figure) or links while improving the wording.

`VietnameseEditor::lint()` is a cheap deterministic check for known patterns; its results go into `ai_meta.flags`. It is a safety net, not the quality check.

### Commands

```bash
php artisan articles:ai-vi-edit 223 --report        # edit an existing VI article in place (backup in storage/app)
php artisan articles:ai-vi-edit 223 --dry-run --report
php artisan articles:ai-vi-eval                     # rubric regression suite (~$3–5)
php artisan articles:ai-vi-eval --only=cmp-win
vendor/bin/phpunit tests/Unit/VietnameseEditorTest.php   # deterministic rules, free
```

### Changing the rules

- New recurring mistake: add an illustrative row to `style-guide-vi.md` §2, a fixture to `tests/vi-fixtures.json`, and, only if it is unambiguous, a regex to `VietnameseEditor::PATTERNS` with a unit test for both a hit and a natural sentence that must not be hit.
- The tables in the guide are examples of error types, not find-and-replace rules. Keep it that way: replacement lists make the model swap one formula for another.
- After editing any file here, run the eval and deploy. The system prompt is cached for an hour, so the first run after a change costs more.

### Limitations

- The reviewer is the same model family as the writer. It catches most translationese, but a native editor should still read pillar pages and anything published under the brand.
- The rubric grader is also a model. Treat a passing eval as "no known regressions", not as proof of quality.
- Edits whose `original` appears twice or not at all are skipped; repeated phrases need a human.
