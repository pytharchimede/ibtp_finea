/* Reusable, dependency-free accessible wizard for FINEA forms. */
(function(){
 'use strict';
 function init(form){
  if(form.dataset.wizardReady==='1')return;
  form.dataset.wizardReady='1';
  const steps=['identity','contact','business'];
  const root=form.closest('.crm-card');
  const panels=steps.map(s=>form.querySelector('[data-crm-panel="'+s+'"]'));
  const markers=steps.map(s=>root.querySelector('[data-crm-tab="'+s+'"]'));
  const prev=form.querySelector('[data-wizard-prev]'),next=form.querySelector('[data-wizard-next]');
  const submit=form.querySelector('[data-wizard-submit]'),counter=form.querySelector('[data-wizard-counter]');
  const error=form.querySelector('[data-wizard-error]');
  let index=0;
  const complete=new Set();
  function valid(i,focus){
   const panel=panels[i];
   const fields=Array.from(panel.querySelectorAll('input,select,textarea')).filter(el=>!el.disabled&&el.type!=='hidden');
   for(const field of fields){
    if(field.required && (!field.value || !String(field.value).trim())){
     if(focus){error.textContent='Veuillez renseigner les champs obligatoires avant de continuer.';focusField(field);}
     return false;
    }
    if(!field.checkValidity()){
     if(focus){error.textContent=field.validationMessage||'Veuillez vérifier ce champ.';focusField(field);}
     return false;
    }
   }
   if(i===0){
    const country=panel.querySelector('[name="identity_country"]');
    const legal=panel.querySelector('[name="legal_form"]');
    const kind=panel.querySelector('[name="identifier_kind"]');
    const cni=panel.querySelector('[name="cni_present"]');
    if(country?.value==='CI'&&legal?.value==='personne_physique'&&(kind?.value!=='CNI'||!cni?.checked)){
     if(focus){error.textContent='Pour une personne physique ivoirienne, renseignez la CNI et confirmez sa présentation.';focusField(cni);}
     return false;
    }
   }
   return true;
  }
  function focusField(field){
   const enhanced=field.closest('.finea-field')?.querySelector('.finea-select-input');
   (enhanced||field).focus();
  }
  function show(i){
   index=i;error.textContent='';
   panels.forEach((p,j)=>{p.hidden=j!==i;});
   markers.forEach((m,j)=>{
    if(j===i)m.setAttribute('aria-current','step');else m.removeAttribute('aria-current');
    m.classList.toggle('is-complete',complete.has(j)&&j!==i);
    const dot=m.querySelector('.crm-step-dot i');
    if(dot)dot.className=complete.has(j)&&j!==i?'fa-solid fa-check':[
     'fa-solid fa-id-card','fa-solid fa-address-book','fa-solid fa-briefcase'][j];
   });
   prev.hidden=i===0;next.hidden=i===steps.length-1;submit.hidden=i!==steps.length-1;
   counter.textContent='Étape '+(i+1)+' sur '+steps.length;
  }
  function advance(){
   if(!valid(index,true))return;
   complete.add(index);
   if(index<steps.length-1)show(index+1);
  }
  next.addEventListener('click',advance);
  prev.addEventListener('click',()=>{if(index>0)show(index-1);});
  form.addEventListener('submit',e=>{
   for(let i=0;i<steps.length;i++){
    if(!valid(i,false)){
     e.preventDefault();complete.delete(i);show(i);valid(i,true);return;
    }
    complete.add(i);
   }
   if(index!==steps.length-1){e.preventDefault();show(steps.length-1);return;}
   submit.disabled=true;
  });
  form.addEventListener('keydown',e=>{
   if(e.key==='Enter'&&e.target.tagName!=='TEXTAREA'&&e.target.tagName!=='BUTTON'){
    e.preventDefault();if(index<steps.length-1)advance();
   }
  });
  form.addEventListener('input',()=>{complete.delete(index);});
  form.addEventListener('change',()=>{complete.delete(index);});
  show(0);
 }
 document.addEventListener('DOMContentLoaded',()=>document.querySelectorAll('form[data-finea-wizard]').forEach(init));
})();
