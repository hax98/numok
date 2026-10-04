// Read-only, loopback-only preview server. No login, mutations or external exposure.
const http=require('node:http'),fs=require('node:fs'),path=require('node:path');
const root=path.resolve(__dirname,'..');
http.createServer((req,res)=>{
 if(!['GET','HEAD'].includes(req.method)){res.writeHead(405).end();return;}
 const route=new URL(req.url,'http://localhost').pathname;
 const file=route==='/'?path.join(root,'reports/creator-preview.html'):route.startsWith('/assets/')?path.join(root,'public',route):null;
 if(!file||!file.startsWith(root+path.sep)||!fs.existsSync(file)){res.writeHead(404).end();return;}
 const type={'.html':'text/html; charset=utf-8','.css':'text/css','.js':'text/javascript','.png':'image/png','.jpg':'image/jpeg','.svg':'image/svg+xml','.webmanifest':'application/manifest+json'}[path.extname(file)]||'application/octet-stream';
 res.setHeader('Content-Type',type);res.setHeader('Cache-Control','no-store');res.end(req.method==='HEAD'?'':fs.readFileSync(file));
}).listen(4867,'127.0.0.1',()=>console.log('Read-only creator preview: http://127.0.0.1:4867'));
