(() => {
  document.addEventListener('DOMContentLoaded', () => {
    const zone=document.querySelector('.btp-workspace [data-dropzone]');
    if(zone){
      const input=zone.querySelector('input[type=file]');
      const preview=zone.querySelector('[data-file-preview]');
      const update=()=>{const f=input?.files?.[0];if(preview)preview.textContent=f?f.name+' · '+(f.size/1024/1024).toFixed(2)+' Mo':'';};
      if(input){
        input.addEventListener('change',update);
        ['dragenter','dragover'].forEach(name=>zone.addEventListener(name,e=>{e.preventDefault();zone.classList.add('is-dragover');}));
        ['dragleave','drop'].forEach(name=>zone.addEventListener(name,()=>zone.classList.remove('is-dragover')));
        zone.addEventListener('drop',e=>{e.preventDefault();if(e.dataTransfer?.files?.length){input.files=e.dataTransfer.files;input.dispatchEvent(new Event('change',{bubbles:true}));}});
      }
    }
    const links=[...document.querySelectorAll('.btp-workspace .btp-tabs a')];
    const setActive=()=>{const hash=location.hash||'#btp-consultation';links.forEach(a=>a.classList.toggle('is-active',a.getAttribute('href')===hash));};
    links.forEach(a=>a.addEventListener('click',()=>{links.forEach(x=>x.classList.remove('is-active'));a.classList.add('is-active');}));
    window.addEventListener('hashchange',setActive);setActive();
  });
})();
