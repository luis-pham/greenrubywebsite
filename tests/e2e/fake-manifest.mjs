// Browser E2E only: two fake website manifests (ports 3201, 3202) reporting en/vi/ja with every section, so the
// local CMS treats Japanese as servable. Secret: e2e-manifest-secret.   node fake-manifest.mjs
import { createServer } from 'node:http';
const SECTIONS = ['home', 'cruise', 'cabin', 'itinerary', 'experiences', 'destinations', 'blog', 'news', 'faq', 'services', 'gallery', 'aboutUs', 'contact', 'booking'];
for (const port of [3201, 3202]) {
  createServer((req, res) => {
    if (req.headers['x-manifest-key'] !== 'e2e-manifest-secret') return res.writeHead(401).end('{}');
    res.writeHead(200, { 'content-type': 'application/json' });
    res.end(JSON.stringify({ schema: 1, buildId: `e2e-${port}`, locales: ['en', 'vi', 'ja'].map((code) => ({ code, routes: true, dictionary: true, sections: SECTIONS })) }));
  }).listen(port, '127.0.0.1');
}
