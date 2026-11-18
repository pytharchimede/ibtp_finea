(() => {
  const split = value => String(value).split(/[,;\r\n]+/u).map(x => x.trim()).filter(Boolean);
  const normalized = value => value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase('fr').trim().replace(/\s+/g, ' ');
  function init(root) {
    if (root.dataset.tagsReady === '1') return;
    const input=root.querySelector('[data-tags-input]'), source=root.querySelector('[data-tags-source]');
    const list=root.querySelector('[data-tags-list]'), add=root.querySelector('[data-tags-add]');
    const feedback=root.querySelector('[data-tags-feedback]'), count=root.querySelector('[data-tags-count]');
    if (!input || !source || !list || !add) return;
    root.dataset.tagsReady='1';
    let tags=[];const max=250;
    const warn=message=>{feedback.textContent=message;};
    const update=()=>{source.value=tags.join(', ');count.textContent=tags.length+' / '+max+' mots-clés';};
    const render=()=>{
      list.replaceChildren();
      tags.forEach((word,index)=>{
        const chip=document.createElement('span');chip.className='finea-keyword-tag';
        const edit=document.createElement('button');edit.type='button';edit.className='finea-keyword-tag-edit';
        edit.textContent=word;edit.title='Modifier '+word;edit.setAttribute('aria-label','Modifier '+word);
        edit.addEventListener('click',()=>editTag(index));
        const remove=document.createElement('button');remove.type='button';remove.className='finea-keyword-tag-remove';
        remove.textContent='×';remove.title='Supprimer '+word;remove.setAttribute('aria-label','Supprimer '+word);
        remove.addEventListener('click',()=>{tags.splice(index,1);warn('');render();});
        chip.append(edit,remove);list.append(chip);
      });
      update();
    };
    const addTags=raw=>{
      warn('');
      let changed=false;
      for(const word of split(raw)){
        if(word.length>120){warn('Un mot-clé ne peut pas dépasser 120 caractères.');continue;}
        if(tags.some(tag=>normalized(tag)===normalized(word)))continue;
        if(tags.length>=max){warn('Maximum '+max+' mots-clés.');break;}
        if([...tags,word].join(', ').length>10000){warn('La liste dépasse 10 000 caractères.');break;}
        tags.push(word);changed=true;
      }
      if(changed)render();
      return changed;
    };
    const commit=()=>{if(!input.value.trim())return;addTags(input.value);input.value='';input.focus();};
    const editTag=index=>{
      const original=tags[index];
      const chip=list.children[index],button=chip?.querySelector('.finea-keyword-tag-edit');
      if(!chip||!button)return;
      const editor=document.createElement('input');editor.type='text';editor.className='finea-keyword-tag-editor';
      editor.maxLength=120;editor.value=original;editor.setAttribute('aria-label','Modifier '+original);
      button.replaceWith(editor);editor.focus();editor.select();
      let closed=false;
      const finish=save=>{
        if(closed)return;closed=true;
        if(save){
          const value=editor.value.trim();
          if(value){
            if(/[,;\r\n]/u.test(value)){warn('Utilisez un mot-clé sans séparateur.');render();return;}
            if(tags.some((tag,i)=>i!==index&&normalized(tag)===normalized(value))){warn('Ce mot-clé existe déjà.');render();return;}
            if([...tags.slice(0,index),value,...tags.slice(index+1)].join(', ').length>10000){warn('La liste dépasse 10 000 caractères.');render();return;}
            tags[index]=value;
          }
        }
        render();
      };
      editor.addEventListener('keydown',event=>{
        if(event.key==='Enter'){event.preventDefault();finish(true);}
        if(event.key==='Escape'){event.preventDefault();finish(false);}
      });
      editor.addEventListener('blur',()=>finish(true),{once:true});
    };
    add.addEventListener('click',commit);
    input.addEventListener('keydown',event=>{
      if(event.key==='Enter'||event.key===','||event.key===';'){event.preventDefault();commit();}
      if(event.key==='Backspace'&&!input.value&&tags.length){tags.pop();render();}
    });
    input.addEventListener('paste',event=>{
      const pasted=event.clipboardData?.getData('text')||'';
      if(/[,;\r\n]/u.test(pasted)){event.preventDefault();addTags(pasted);input.focus();}
    });
    root.closest('form')?.addEventListener('submit',()=>{if(input.value.trim())commit();update();});
    const original=split(source.value);
    tags=Array.from(new Map(original.map(word=>[normalized(word),word])).values()).slice(0,max);
    // Avoid silently discarding malformed/oversized legacy values on first load.
    if(original.length>max||tags.some(word=>word.length>120)||tags.join(', ').length>10000){
      warn('Liste existante trop longue : corrigez les valeurs dans le champ texte avant de sauvegarder.');
      root.classList.add('has-invalid-source');
      return;
    }
    render();root.classList.add('is-enhanced');
  }
  const start=()=>document.querySelectorAll('[data-keyword-tags]').forEach(init);
  if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',start);else start();
})();
