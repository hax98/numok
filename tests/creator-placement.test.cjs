const test=require('node:test'),assert=require('node:assert/strict'),vm=require('node:vm'),fs=require('node:fs');
const source=fs.readFileSync(require('node:path').join(__dirname,'../public/assets/js/creator-hub.js'),'utf8');
function harness({name='',confirm=true,reduced=false,noForm=false}={}){
 const nodes={'content-link-form':{scrollIntoView(value){this.scrolled=value}},'content-title':{value:name,focus(){this.focused=true}},'content-platform':{value:'youtube'},'content-kind':{value:'tutorial'},'content-link-details':{open:false},'placement-status':{textContent:''}};
 let listener;vm.runInNewContext(source,{document:{addEventListener(_,fn){listener=fn},getElementById(id){return noForm&&id==='content-link-form'?null:nodes[id]}},window:{confirm(){return confirm},matchMedia(){return {matches:reduced}}},setTimeout});
 return {nodes,click(key,disabled=false){return listener({target:{closest(selector){return selector==='[data-placement]'?{dataset:{placement:key},disabled}:null}}})}};
}
for(const [key,platform,kind] of [['instagram-reel','instagram','video'],['instagram-story','instagram','story'],['tiktok-bio','tiktok','post']])test(key+' selects correct placement without submitting',async()=>{const h=harness();await h.click(key);assert.equal(h.nodes['content-platform'].value,platform);assert.equal(h.nodes['content-kind'].value,kind);assert.equal(h.nodes['content-title'].value,'');assert.equal(h.nodes['content-link-details'].open,true);assert.equal(h.nodes['content-title'].focused,true);assert.match(h.nodes['placement-status'].textContent,/selected/)});
test('retains draft name when switching',async()=>{const h=harness({name:'My existing demo'});await h.click('instagram-story');assert.equal(h.nodes['content-title'].value,'My existing demo')});
test('cancel preserves draft placement',async()=>{const h=harness({name:'Existing',confirm:false});await h.click('instagram-story');assert.equal(h.nodes['content-platform'].value,'youtube');assert.equal(h.nodes['content-link-details'].open,false)});
test('read-only and unjoined previews do not act',async()=>{for(const h of [harness(),harness({noForm:true})]){await h.click('instagram-reel',true);assert.equal(h.nodes['content-platform'].value,'youtube')}});
test('unknown placement is ignored',async()=>{const h=harness();await h.click('unknown');assert.equal(h.nodes['content-platform'].value,'youtube')});
test('respects reduced motion',async()=>{const h=harness({reduced:true});await h.click('tiktok-bio');assert.equal(h.nodes['content-link-form'].scrolled.behavior,'instant')});
