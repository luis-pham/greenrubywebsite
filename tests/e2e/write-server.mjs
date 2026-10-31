// Serves the built writing app (greenruby-write/dist) and proxies its API paths to the E2E CMS, as Nginx does in
// production. Test harness only.   node write-server.mjs <dist> <port> <cms origin>
import { createServer, request } from 'node:http';
import { readFile } from 'node:fs/promises';
import { extname, join, normalize } from 'node:path';

const [dist, port, cms] = process.argv.slice(2);
const types = { '.html': 'text/html', '.js': 'text/javascript', '.css': 'text/css', '.svg': 'image/svg+xml', '.png': 'image/png', '.json': 'application/json' };
const target = new URL(cms);

createServer(async (req, res) => {
  if (/^\/(api|upload|thumbnail)\//.test(req.url)) {
    const proxied = request({ host: target.hostname, port: target.port, path: req.url, method: req.method, headers: { ...req.headers, host: target.host } }, (r) => {
      res.writeHead(r.statusCode, r.headers);
      r.pipe(res);
    });
    req.pipe(proxied);
    return;
  }
  const path = normalize(decodeURIComponent(new URL(req.url, 'http://x').pathname)).replace(/^(\.\.[/\\])+/, '');
  const file = join(dist, path);
  let body, type;
  try {
    [body, type] = [await readFile(file), types[extname(file)] ?? 'application/octet-stream'];
  } catch {
    [body, type] = [await readFile(join(dist, 'index.html')), 'text/html']; // SPA routes
  }
  res.writeHead(200, { 'Content-Type': type }).end(body);
}).listen(Number(port), '127.0.0.1');
