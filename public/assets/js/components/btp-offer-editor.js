(()=>{document.addEventListener('DOMContentLoaded',()=>{
const form=document.getElementById('btp-offer-form');if(!form)return;
const lines=document.getElementById('btp-offer-lines'),tasks=document.getElementById('btp-offer-tasks');
const units=JSON.parse(document.getElementById('btp-units-data')?.textContent||'[]');
let lineId=0,taskId=0,currentGroup='';
const input=(name,type,placeholder,step)=>{const el=document.createElement('input');el.name=name;el.type=type;el.placeholder=placeholder||'';el.required=true;if(type==='number'){el.min='0';el.step=step||'any';}return el;};
const money=n=>new Intl.NumberFormat('fr-FR',{maximumFractionDigits:2}).format(n)+' FCFA';
const calc=()=>{let ht=0;lines.querySelectorAll('tr[data-quote-line]').forEach(tr=>{const q=Number(tr.querySelector('[name$="[quantity]"]')?.value||0),p=Number(tr.querySelector('[name$="[unit_price]"]')?.value||0),sum=Math.round(q*p*100)/100;ht+=sum;tr.querySelector('[data-line-total]').textContent=money(sum);});const discount=Number(form.querySelector('[data-discount]').value||0),net=Math.max(0,ht-discount),vat=form.querySelector('[data-vat]').checked?Math.round(net*.18*100)/100:0;form.querySelector('[data-total-ht]').textContent=money(net);form.querySelector('[data-total-vat]').textContent=money(vat);form.querySelector('[data-total-ttc]').textContent=money(net+vat);};
const addGroup=()=>{const name=prompt('Intitulé du groupe de travaux :',currentGroup||'Gros œuvre');if(!name?.trim())return;currentGroup=name.trim().slice(0,180);const tr=document.createElement('tr');tr.className='btp-group-heading';const td=document.createElement('td');td.colSpan=7;const strong=document.createElement('strong');strong.textContent=currentGroup;td.appendChild(strong);tr.appendChild(td);lines.appendChild(tr);addLine();};
const addLine=()=>{const i=lineId++,tr=document.createElement('tr');tr.dataset.quoteLine='';const groupTd=document.createElement('td'),group=input('lines['+i+'][group_name]','text','Sans groupe');group.required=false;group.value=currentGroup;groupTd.appendChild(group);tr.appendChild(groupTd);
const desc=document.createElement('td');desc.appendChild(input('lines['+i+'][description]','text','Désignation'));tr.appendChild(desc);
const unitTd=document.createElement('td'),select=document.createElement('select');select.name='lines['+i+'][unit]';select.required=true;select.dataset.selectSearch='';select.dataset.placeholder='Rechercher une unité';const blank=document.createElement('option');blank.value='';blank.textContent='Choisir';select.appendChild(blank);units.forEach(u=>{const opt=document.createElement('option');opt.value=u.code;opt.textContent=u.label+' ('+u.code+')';select.appendChild(opt);});unitTd.appendChild(select);tr.appendChild(unitTd);
for(const [field,placeholder,step] of [['quantity','1','.001'],['unit_price','0','.01']]){const td=document.createElement('td');td.appendChild(input('lines['+i+']['+field+']','number',placeholder,step));tr.appendChild(td);}
const total=document.createElement('td');total.dataset.lineTotal='';total.textContent=money(0);tr.appendChild(total);
const td=document.createElement('td'),remove=document.createElement('button');remove.type='button';remove.textContent='×';remove.setAttribute('aria-label','Supprimer la ligne');remove.addEventListener('click',()=>{tr.remove();calc();});td.appendChild(remove);tr.appendChild(td);lines.appendChild(tr);
if(window.FineaFormComponents?.enhanceSelect)window.FineaFormComponents.enhanceSelect(select);
};
const addTask=()=>{const i=taskId++,tr=document.createElement('tr');for(const [field,type,placeholder] of [['label','text','Phase des travaux'],['start_date','date',''],['end_date','date','']]){const td=document.createElement('td');td.appendChild(input('planning['+i+']['+field+']',type,placeholder));tr.appendChild(td);}const td=document.createElement('td'),remove=document.createElement('button');remove.type='button';remove.textContent='×';remove.addEventListener('click',()=>tr.remove());td.appendChild(remove);tr.appendChild(td);tasks.appendChild(tr);};
const issuedField=form.querySelector('[name=issue_date]'),durationField=form.querySelector('[name=validity_days]'),endField=form.querySelector('[name=validity_date]');
const parseDate=value=>{if(!/^\\d{4}-\\d{2}-\\d{2}$/.test(value))return null;const date=new Date(value+'T00:00:00Z');return Number.isFinite(date.getTime())&&date.toISOString().slice(0,10)===value?date:null;};
const formatDate=date=>date.toISOString().slice(0,10);
const daysBetween=(start,end)=>Math.round((end.getTime()-start.getTime())/86400000);
const shiftDate=(date,days)=>{const copy=new Date(date.getTime());copy.setUTCDate(copy.getUTCDate()+days);return formatDate(copy);};
let lastEdited='duration';
const syncValidity=changed=>{
  if(changed==='issue'||changed==='duration'||changed==='end')lastEdited=changed;
  const start=parseDate(issuedField.value),end=parseDate(endField.value);
  const raw=durationField.value,days=raw!==''?Number(raw):NaN;
  const validDays=Number.isInteger(days)&&days>=0&&days<=3650;
  if(lastEdited==='end'){
    if(start&&end){const diff=daysBetween(start,end);durationField.value=diff>=0&&diff<=3650?String(diff):'';}
    else if(end&&validDays)issuedField.value=shiftDate(end,-days);
  }else if(lastEdited==='issue'){
    if(end&&validDays){issuedField.value=start?issuedField.value:shiftDate(end,-days);if(start)durationField.value=String(daysBetween(start,end));}
    else if(start&&validDays)endField.value=shiftDate(start,days);
  }else{
    if(start&&validDays)endField.value=shiftDate(start,days);
    else if(end&&validDays)issuedField.value=shiftDate(end,-days);
    else if(start&&end){const diff=daysBetween(start,end);durationField.value=diff>=0&&diff<=3650?String(diff):'';}
  }
};
form.querySelector('[data-add-line]').addEventListener('click',addLine);form.querySelector('[data-add-group]').addEventListener('click',addGroup);form.querySelector('[data-add-task]').addEventListener('click',addTask);form.addEventListener('input',e=>{calc();if(e.target===issuedField)syncValidity('issue');else if(e.target===durationField)syncValidity('duration');else if(e.target===endField)syncValidity('end');});form.addEventListener('change',e=>{calc();if(e.target===issuedField)syncValidity('issue');else if(e.target===durationField)syncValidity('duration');else if(e.target===endField)syncValidity('end');});addLine();addTask();calc();syncValidity('duration');
});})();