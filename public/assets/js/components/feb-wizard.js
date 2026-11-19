(()=>{'use strict';
document.addEventListener('DOMContentLoaded',()=>{
 const form=document.getElementById('feb-wizard-form');if(!form)return;
 const scope=form.dataset.scope;
 const steps=[...form.querySelectorAll('[data-wizard-step]')],tabs=[...document.querySelectorAll('.feb-employee-create [data-wizard-nav]')],prev=form.querySelector('[data-wizard-prev]'),next=form.querySelector('[data-wizard-next]'),progress=form.querySelector('[data-wizard-progress]'),fill=document.querySelector('.feb-employee-create [data-wizard-fill]'),review=form.querySelector('#feb-wizard-review');
 const money=n=>new Intl.NumberFormat('fr-FR',{maximumFractionDigits:0}).format(n)+' FCFA';
 const field=name=>form.querySelector('[name="'+name+'"]');
 const selectedText=el=>el?.selectedOptions?.[0]?.textContent.trim()||'';
 const rows=()=>[...form.querySelectorAll('[data-feb-line]')];
 let current=0;
 const refreshTotal=()=>{
  let sum=0;
  rows().forEach(row=>{
   const desc=row.querySelector('[name$="[description]"]')?.value.trim();
   const qty=Number(row.querySelector('.feb-quantity')?.value||0);
   const price=Number(row.querySelector('.feb-price')?.value||0);
   const amount=desc&&Number.isFinite(qty)&&Number.isFinite(price)?Math.max(0,qty*price):0;
   sum+=amount;
   row.querySelector('[data-feb-line-total]').textContent=money(amount);
  });
  const output=form.querySelector('#feb-grand-total');if(output)output.textContent=money(sum);
  return sum;
 };
 const lineBox=form.querySelector('#feb-lines');
 const collapse=row=>{row.classList.add('is-collapsed');row.querySelector('[data-feb-line-body]').hidden=true;row.querySelector('[data-feb-toggle]').setAttribute('aria-expanded','false');};
 const expand=row=>{rows().forEach(other=>{if(other!==row)collapse(other);});row.classList.remove('is-collapsed');row.querySelector('[data-feb-line-body]').hidden=false;row.querySelector('[data-feb-toggle]').setAttribute('aria-expanded','true');};
 const syncOffice=()=>{
  const service=form.querySelector('#feb-office-service'),allocation=form.querySelector('#feb-office-allocation');if(!service||!allocation)return;
  for(const option of [...allocation.options].slice(1)){
   option.hidden=option.dataset.service!==service.value;
   option.disabled=option.hidden;
  }
  if(allocation.selectedOptions[0]?.disabled)allocation.value='';
 };
 form.querySelector('#feb-office-service')?.addEventListener('change',syncOffice);syncOffice();
 const updateLineTitle=row=>{
  const title=row.querySelector('[data-feb-line-title]');if(!title)return;
  const index=rows().indexOf(row)+1;
  const description=row.querySelector('[name$="[description]"]')?.value.trim()||'';
  title.textContent=description||'Besoin '+index;
  title.title=description||'Besoin '+index;
 };
 const initRow=row=>{
  const quote=row.querySelector('[data-quote-line]'),debourse=row.querySelector('[data-debourse]'),line=row.querySelector('[data-budget-line]');
  if(quote&&debourse&&line){
   const sync=(reset=false)=>{
    const allowedBudgets=new Set([...line.options].filter(o=>o.value&&o.dataset.quote===quote.value).map(o=>o.dataset.debourse));
    for(const option of [...debourse.options].slice(1)){option.hidden=!allowedBudgets.has(option.value);option.disabled=option.hidden;}
    if(reset||debourse.selectedOptions[0]?.disabled)debourse.value='';
    for(const option of [...line.options].slice(1)){
     option.hidden=option.dataset.quote!==quote.value||option.dataset.debourse!==debourse.value;
     option.disabled=option.hidden;
    }
    if(reset||line.selectedOptions[0]?.disabled)line.value='';
    debourse.disabled=!quote.value;line.disabled=!debourse.value;
   };
   quote.addEventListener('change',()=>sync(true));
   debourse.addEventListener('change',()=>sync(false));
   sync(true);
  }
  row.addEventListener('input',()=>{refreshTotal();updateLineTitle(row);});
  updateLineTitle(row);
  row.querySelector('[data-feb-toggle]')?.addEventListener('click',()=>row.classList.contains('is-collapsed')?expand(row):collapse(row));
  row.querySelector('[data-feb-remove]')?.addEventListener('click',()=>{if(rows().length<=1)return;row.remove();rows().forEach((r,i)=>{updateLineTitle(r);r.querySelectorAll('[name^="lines["]').forEach(el=>el.name=el.name.replace(/^lines\\[\\d+\\]/,'lines['+i+']'));});expand(rows()[rows().length-1]);refreshTotal();});
 };
 rows().forEach(initRow);
 form.querySelector('#feb-add-line')?.addEventListener('click',()=>{
  const first=rows()[0],index=rows().length;
  if(index>=30){alert('Maximum 30 besoins par FEB.');return;}
  const clone=first.cloneNode(true);
  clone.querySelectorAll('[name^="lines["]').forEach(el=>{el.name=el.name.replace(/^lines\\[\\d+\\]/,'lines['+index+']');if(el.tagName==='SELECT')el.selectedIndex=0;else if(el.classList.contains('feb-quantity'))el.value='1';else if(el.classList.contains('feb-price')||el.name.endsWith('[description]'))el.value='';});
  clone.querySelector('[data-feb-line-title]').textContent='Besoin '+(index+1);
  clone.querySelector('[data-feb-remove]').hidden=false;
  clone.querySelector('[data-feb-line-total]').textContent=money(0);
  clone.querySelector('[data-feb-line-body]').hidden=false;
  lineBox.append(clone);initRow(clone);expand(clone);refreshTotal();
  clone.scrollIntoView({behavior:'smooth',block:'nearest'});
 });
 refreshTotal();
 const attachmentInput=form.querySelector('#feb-attachments'),dropzone=form.querySelector('#feb-dropzone'),fileList=form.querySelector('#feb-file-list');
 let attachments=[];
 const renderAttachments=()=>{
  if(!attachmentInput||!fileList)return;
  const transfer=new DataTransfer();attachments.forEach(file=>transfer.items.add(file));attachmentInput.files=transfer.files;
  fileList.replaceChildren();
  attachments.forEach((file,index)=>{
   const li=document.createElement('li'),name=document.createElement('span'),remove=document.createElement('button');
   name.textContent=file.name+' · '+(file.size/1048576).toFixed(2)+' Mo';remove.type='button';remove.textContent='Retirer';
   remove.addEventListener('click',()=>{attachments.splice(index,1);renderAttachments();});li.append(name,remove);fileList.append(li);
  });
 };
 const addAttachments=files=>{
  const allowed=['application/pdf','image/jpeg','image/png','application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
  for(const file of files){
   if(!allowed.includes(file.type)||file.size<1||file.size>10485760){alert('Fichier non autorisé ou supérieur à 10 Mo : '+file.name);continue;}
   if(attachments.length>=10){alert('Maximum 10 fichiers par FEB.');break;}
   if(!attachments.some(f=>f.name===file.name&&f.size===file.size&&f.lastModified===file.lastModified))attachments.push(file);
  }
  renderAttachments();
 };
 attachmentInput?.addEventListener('change',()=>addAttachments([...attachmentInput.files]));
 if(dropzone){
  ['dragenter','dragover'].forEach(type=>dropzone.addEventListener(type,e=>{e.preventDefault();dropzone.classList.add('is-dragover');}));
  ['dragleave','drop'].forEach(type=>dropzone.addEventListener(type,e=>{e.preventDefault();dropzone.classList.remove('is-dragover');}));
  dropzone.addEventListener('drop',e=>addAttachments([...e.dataTransfer.files]));
 }
 const show=i=>{
  current=Math.max(0,Math.min(steps.length-1,i));
  steps.forEach((el,j)=>el.hidden=j!==current);
  tabs.forEach((el,j)=>{
   el.classList.toggle('is-active',j===current);el.classList.toggle('is-visited',j<current);
   if(j===current)el.setAttribute('aria-current','step');else el.removeAttribute('aria-current');
  });
  if(prev)prev.disabled=current===0;
  if(next)next.hidden=current===steps.length-1;
  if(progress)progress.textContent='Étape '+(current+1)+' / '+steps.length;
  if(fill)fill.style.width=(current/Math.max(1,steps.length-1)*100)+'%';
  if(current===steps.length-1)refreshReview();
 };
 const checkField=el=>{
  if(el.disabled||el.type==='hidden')return true;
  if(el.required&&!el.value){el.focus();el.reportValidity();return false;}
  if(el.value&&!el.checkValidity()){el.focus();el.reportValidity();return false;}
  return true;
 };
 const validate=i=>{
  const section=steps[i];if(!section)return false;
  if(i===3&&attachments.length===0){alert('Ajoutez au moins un justificatif.');attachmentInput?.focus();return false;}
  if(i===1){
   let count=0;
   for(const row of rows()){
    const description=row.querySelector('[name$="[description]"]'),qty=row.querySelector('.feb-quantity'),price=row.querySelector('.feb-price');
    const quote=row.querySelector('[data-quote-line]'),debourse=row.querySelector('[data-debourse]'),line=row.querySelector('[data-budget-line]');
    const filled=!!description.value.trim()||!!price.value||!!quote?.value||!!debourse?.value||!!line?.value;
    if(!filled)continue;
    count++;
    if(!description.value.trim()||!qty.value||Number(qty.value)<=0||price.value===''||Number(price.value)<0||!Number.isFinite(Number(price.value))){expand(row);description.focus();alert('Complétez la désignation, la quantité et le prix de chaque besoin.');return false;}
    if(scope==='chantier'&&(!quote?.value||!debourse?.value||!line?.value)){expand(row);(quote||description).focus();alert('Choisissez la ligne de devis, le déboursé puis la ligne de déboursé.');return false;}
   }
   if(!count){alert('Ajoutez au moins un besoin.');return false;}
   if(refreshTotal()<=0){alert('Le montant total doit être positif.');return false;}
  }
  for(const el of section.querySelectorAll('input,select,textarea'))if(!checkField(el))return false;
  if(i===0&&scope==='bureau'&&!field('office_allocation_id')?.value){alert('Sélectionnez un poste budgétaire validé pour le service.');return false;}
  return true;
 };
 const refreshReview=()=>{
  if(!review)return;review.replaceChildren();
  const entries=[
   ['Périmètre',scope==='bureau'?'FEB Bureau':'FEB Chantier'],
   ['Affectation',scope==='bureau'?selectedText(field('office_allocation_id')): 'Chantier #'+field('chantier_id').value],
   ['Circuit','Circuit défini dans les paramètres FEB'],
   ['Bénéficiaire',field('beneficiary_name')?.value||''],
   ['Date souhaitée',field('needed_at')?.value||''],
   ['Motif',field('purpose')?.value||''],
   ['Montant estimé',money(refreshTotal())],
   ['Documents',attachments.map(f=>f.name).join(', ')||'Absents'],
  ];
  if(scope==='bureau')entries.splice(2,0,['Service bénéficiaire',selectedText(field('service_id'))]);
  for(const [label,value] of entries){
   const p=document.createElement('p'),strong=document.createElement('strong');
   strong.textContent=label+' : ';p.append(strong,document.createTextNode(value));review.append(p);
  }
 };
 next?.addEventListener('click',()=>{if(validate(current))show(current+1);});
 prev?.addEventListener('click',()=>show(current-1));
 tabs.forEach((tab,i)=>tab.addEventListener('click',()=>{if(i<=current)show(i);else if(i===current+1&&validate(current))show(i);}));
 form.addEventListener('submit',event=>{
  for(let i=0;i<steps.length-1;i++){
   if(!validate(i)){event.preventDefault();show(i);return;}
  }
 });
 show(0);
});
})();