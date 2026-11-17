<?php
namespace Modules\BackEnd\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\BackEnd\Entities\AppJob;

/** Job postings (app_job) for the admin "Tuyển dụng". Public reads go through App\Services\Content\CareerContentService. */
class AppJobService
{
    /** Form fields copied as-is onto the row ('' => null). */
    private const FIELDS = [
        'title', 'department', 'employment_type', 'location', 'address_locality', 'address_region', 'summary',
        'intro', 'description', 'requirements', 'benefits', 'apply_email', 'status', 'posted_at', 'deadline',
        'seo_title', 'seo_description', 'ord',
    ];

    public static function find($id)
    {
        return AppJob::where('id', $id)->first();
    }

    public static function create(array $data): int
    {
        return DB::transaction(function () use ($data) {
            $obj = new AppJob();
            self::fill($obj, $data);
            if (!$obj->ord) {
                $obj->ord = (int) AppJob::max('ord') + 1;
            }
            $obj->created_at = now();
            $obj->save();

            return (int) $obj->id;
        });
    }

    public static function update(array $data): void
    {
        $obj = self::find($data['id']);
        if (!$obj) {
            return;
        }
        self::fill($obj, $data);
        $obj->updated_at = now(); // sitemap lastmod
        $obj->save();
    }

    /** Close or reopen a posting from the list (status closed <-> published). */
    public static function setStatus($id, string $status): void
    {
        $obj = self::find($id);
        if (!$obj || !in_array($status, ['published', 'closed'], true)) {
            return;
        }
        $obj->status = $status;
        if ($status === 'published' && !$obj->posted_at) {
            $obj->posted_at = now()->toDateString();
        }
        $obj->updated_at = now();
        $obj->save();
    }

    public static function delete($id): void
    {
        AppJob::whereIn('id', (array) $id)->delete();
    }

    public static function getPaging(array $param)
    {
        $list = AppJob::query();
        if (!empty($param['keyword'])) {
            $list->where(function ($query) use ($param) {
                $query->where('title', 'like', '%' . $param['keyword'] . '%')
                    ->orWhere('department', 'like', '%' . $param['keyword'] . '%')
                    ->orWhere('slug', 'like', '%' . $param['keyword'] . '%');
            });
        }
        if (!empty($param['status'])) {
            $list->where('status', $param['status']);
        }

        return $list->orderBy('ord')->orderByDesc('id')->get();
    }

    public static function saveOrder(array $list): void
    {
        DB::transaction(function () use ($list) {
            foreach ($list as $id => $ord) {
                AppJob::where('id', $id)->update(['ord' => (int) $ord]);
            }
        });
    }

    /** True when the last day to apply is before today (Vietnam time, config app.timezone). */
    public static function isExpired($job): bool
    {
        return $job->deadline && substr((string) $job->deadline, 0, 10) < now()->toDateString();
    }

    /** URL slug: lowercase ASCII words joined by "-" (Vietnamese diacritics removed, đ -> d). */
    public static function slugify(?string $value): string
    {
        return Str::slug(str_replace(['đ', 'Đ'], 'd', (string) $value));
    }

    public static function slugTaken(string $slug, $exceptId = null): bool
    {
        return AppJob::where('slug', $slug)
            ->when($exceptId, fn ($q) => $q->where('id', '!=', $exceptId))
            ->exists();
    }

    private static function fill(AppJob $obj, array $data): void
    {
        foreach (self::FIELDS as $field) {
            if (array_key_exists($field, $data)) {
                $value = is_string($data[$field]) ? trim($data[$field]) : $data[$field];
                $obj->{$field} = $value === '' ? null : $value;
            }
        }
        $obj->slug = self::slugify(($data['slug'] ?? '') ?: $obj->title);
        $obj->ord = (int) $obj->ord;
        // datePosted: the day it was first published, unless the editor set one.
        if ($obj->status === 'published' && !$obj->posted_at) {
            $obj->posted_at = now()->toDateString();
        }
    }
}
