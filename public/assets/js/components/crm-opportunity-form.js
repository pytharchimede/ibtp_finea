/* Progressive enhancement for the reusable PHP opportunity form. */
(() => {
 const init=()=>{
  const zone=document.querySelector('[data-opp-dropzone]');
  const input=zone?.querySelector('[data-opp-files]');
  const list=document.querySelector('[data-opp-file-list]');
  const error=document.querySelector('[data-opp-file-error]');
  if(!zone||!input||!list||!error)return;
  const allowed=new Set(['application/pdf','image/jpeg','image/png','application/vnd.openxmlformats-officedocument.wordprocessingml.document','application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
  const max=5,limit=10*1024*1024;
  let files=[];
  const sync=()=>{const dt=new DataTransfer();files.forEach(f=>dt.items.add(f));input.files=dt.files;};
  const validate=arr=>{
   error.textContent='';
   const next=[...files];
   for(const file of arr){
    if(next.length>=max){error.textContent='Maximum 5 pièces jointes.';break;}
    if(!allowed.has(file.type)){error.textContent='Format non autorisé : '+file.name;continue;}
    if(file.size===0||file.size>limit){error.textContent='Fichier vide ou supérieur à 10 Mo : '+file.name;continue;}
    if(next.some(f=>f.name===file.name&&f.size===file.size&&f.lastModified===file.lastModified))continue;
    next.push(file);
   }
   files=next;sync();render();
  };
  const render=()=>{
   list.replaceChildren();
   files.forEach((file,index)=>{
    const item=document.createElement('div');item.className='opp-file-item';
    const thumb=document.createElement('div');thumb.className='opp-file-thumb';
    const extension=file.name.split('.').pop()?.toUpperCase()||'DOC';
    thumb.textContent=extension;
    if(file.type.startsWith('image/')){
     const url=URL.createObjectURL(file),img=document.createElement('img');img.src=url;img.alt='Aperçu '+file.name;img.addEventListener('load',()=>URL.revokeObjectURL(url),{once:true});img.addEventListener('error',()=>URL.revokeObjectURL(url),{once:true});thumb.replaceChildren(img);
    }
    const info=document.createElement('div');info.className='opp-file-info';
    const name=document.createElement('strong');name.textContent=file.name;name.title=file.name;
    const size=document.createElement('small');size.textContent=(file.size/1024/1024).toFixed(2)+' Mo';
    info.append(name,size);
    const remove=document.createElement('button');remove.type='button';remove.className='opp-file-remove';remove.textContent='×';remove.setAttribute('aria-label','Retirer '+file.name);
    remove.addEventListener('click',()=>{files.splice(index,1);sync();render();error.textContent='';});
    item.append(thumb,info,remove);list.appendChild(item);
   });
  };
  input.addEventListener('change',()=>{const selected=[...input.files];validate(selected);});
  ['dragenter','dragover'].forEach(type=>zone.addEventListener(type,e=>{e.preventDefault();zone.classList.add('is-dragging');}));
  ['dragleave','drop'].forEach(type=>zone.addEventListener(type,e=>{e.preventDefault();zone.classList.remove('is-dragging');}));
  zone.addEventListener('drop',e=>{if(e.dataTransfer?.files?.length)validate([...e.dataTransfer.files]);});
  const date=document.getElementById('opp-deadline');
  document.querySelector('[data-opp-calendar]')?.addEventListener('click',()=>{if(typeof date?.showPicker==='function'){try{date.showPicker();return;}catch(_){}}date?.focus();});
 };
 document.addEventListener('DOMContentLoaded',init);
})();
