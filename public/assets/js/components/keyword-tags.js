(() => {
  const split = value => value.split(/[,;\\r\\n]+/u).map(x => x.trim()).filter(Boolean);
  const normalized = value => value.normalize('NFD').replace(/[\\u0300-\\u036f]/g, '').toLocaleLowerCase('fr').trim().replace(/\\s+/g, ' ');
  function init(root) {
    if (root.dataset.tagsReady === '1') return;
    root.dataset.tagsReady = '1';
    const input=root.querySelector('[data-tags-input]'), source=root.querySelector('[data-tags-source]');
    const list=root.querySelector('[data-tags-list]'), add=root.querySelector('[data-tags-add]');
    const feedback=root.querySelector('[data-tags-feedback]'), count=root.querySelector('[data-tags-count]');
    if (!input || !source || !list || !add) return;
    let tags=[];const max=250;
    const update=()=>{source.value=tags.join(', ');count.textContent=tags.length+' / '+max+' mots-clés';};
    const warn=message=>{feedback.textContent=message;};
    const render=()=>{
      list.replaceChildren();
      tags.forEach((word,index)=>{
        const chip=document.createElement('span');chip.className='finea-keyword-tag';
        const edit=document.createElement('button');edit.type='button';edit.className='finea-keyword-tag-edit';edit.textContent=word;
        edit.title='Modifier '+word;edit.setAttribute('aria-label','Modifier '+word);
        edit.addEventListener('click',()=>editTag(index));
        const remove=document.createElement('button');remove.type='button';remove.className='finea-keyword-tag-remove';
        remove.textContent='×';remove.title='Supprimer '+word;remove.setAttribute('aria-label','Supprimer '+word);
        remove.addEventListener('click',()=>{tags.splice(index,1);warn('');render();});
        chip.append(edit,remove);list.append(chip);
      });
      update();
    };
    const addTags=raw=>{
      let added=0;warn('');
      for(const value of split(raw)){
        if(value.length>120){warn('Un mot-clé ne peut pas dépasser 120 caractères.');continue;}
        if(tags.some(tag=>normalized(tag)===normalized(value)))continue;
        if(tags.length>=max){warn('Maximum '+max+' mots-clés.');break;}
        tags.push(value);added++;
      }
      if(source.value.length>10000){tags.splice(-added);warn('La liste dépasse 10 000 caractères.');}
      render();return added;
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
            if(/[,;\\r\\n]/u.test(value)){warn('Utilisez un mot-clé sans séparateur.');render();return;}
            if(tags.some((tag,i)=>i!==index&&normalized(tag)===normalized(value))){warn('Ce mot-clé existe déjà.');render();return;}
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
      if(/[,;\\r\\n]/u.test(pasted)){event.preventDefault();addTags(pasted);input.focus();}
    });
    const form=root.closest('form');
    form?.addEventListener('submit',()=>{if(input.value.trim())commit();update();});
    // The original textarea remains usable without JavaScript; hide it only after enhancement.
    tags=Array.from(new Map(split(source.value).map(word=>[normalized(word),word])).values()).slice(0,max);
    render();root.classList.add('is-enhanced');
  }
  document.addEventListener('DOMContentLoaded',()=>document.querySelectorAll('[data-keyword-tags]').forEach(init));
})();
