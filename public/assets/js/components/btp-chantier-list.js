(()=>{document.addEventListener('DOMContentLoaded',()=>{
const select=document.getElementById('btp-chantier-project'),link=document.getElementById('btp-chantier-open');
if(select&&link){const base=link.dataset.wizardBase||link.href.replace(/\/nouveau\/?$/,'');const update=()=>{link.href=select.value?base+'/projet/'+encodeURIComponent(select.value):base;};select.addEventListener('change',update);update();}
const form=document.getElementById('btp-chantier-search-form');if(!form)return;
const cards=[...document.querySelectorAll('[data-chantier-card]')],result=document.getElementById('btp-chantier-results'),empty=document.getElementById('btp-chantier-no-results'),reset=document.getElementById('btp-chantier-reset');
const normalize=s=>(s||'').normalize('NFD').replace(/[\u0300-\u036f]/g,'').toLocaleLowerCase().trim();
const filter=()=>{const f=new FormData(form),q=normalize(f.get('q')),status=f.get('status'),project=f.get('project'),client=f.get('client'),from=f.get('from'),to=f.get('to');let count=0;
cards.forEach(card=>{const ok=(!q||normalize(card.dataset.search).includes(q))&&(!status||card.dataset.status===status)&&(!project||card.dataset.project===project)&&(!client||card.dataset.client===client)&&(!from||card.dataset.created>=from)&&(!to||card.dataset.created<=to);card.hidden=!ok;if(ok)count++;});
result.textContent=count+' chantier(s) sur '+cards.length;empty.hidden=count!==0||cards.length===0;};
form.addEventListener('input',filter);form.addEventListener('change',filter);form.addEventListener('submit',event=>event.preventDefault());reset?.addEventListener('click',()=>{form.reset();filter();});filter();
});})();