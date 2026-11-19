(() => {
  document.addEventListener('DOMContentLoaded', () => {
    const zone=document.getElementById('btp-bulk-dropzone');
    if(zone){
      const input=document.getElementById('btp-document-files'),preview=document.getElementById('btp-bulk-preview');
      const render=()=>{
        const files=[...(input.files||[])];
        preview.replaceChildren();
        if(!files.length){preview.textContent='Aucun fichier sélectionné';return;}
        const summary=document.createElement('strong');
        summary.textContent=files.length+' fichier(s) sélectionné(s)';
        preview.appendChild(summary);
        const list=document.createElement('ul');
        files.forEach(file=>{const li=document.createElement('li');li.textContent=file.name+' · '+(file.size/1024/1024).toFixed(2)+' Mo';list.appendChild(li);});
        preview.appendChild(list);
      };
      input.addEventListener('change',render);
      ['dragenter','dragover'].forEach(type=>zone.addEventListener(type,e=>{e.preventDefault();zone.classList.add('is-dragover');}));
      ['dragleave','drop'].forEach(type=>zone.addEventListener(type,e=>{e.preventDefault();zone.classList.remove('is-dragover');}));
      zone.addEventListener('drop',e=>{e.preventDefault();if(e.dataTransfer?.files?.length){input.files=e.dataTransfer.files;render();}});
    }
    const links=[...document.querySelectorAll('.btp-workspace .btp-tabs a')];
    const setActive=()=>{const hash=location.hash||'#btp-consultation';links.forEach(a=>a.classList.toggle('is-active',a.getAttribute('href')===hash));};
    links.forEach(a=>a.addEventListener('click',()=>{links.forEach(x=>x.classList.remove('is-active'));a.classList.add('is-active');}));
    window.addEventListener('hashchange',setActive);setActive();
  });
})();
