(()=>{document.addEventListener('DOMContentLoaded',()=>{
const form=document.getElementById('btp-offer-form');if(!form)return;
const lines=document.getElementById('btp-offer-lines');
const units=JSON.parse(document.getElementById('btp-units-data')?.textContent||'[]');
let lineId=0,taskId=0,currentGroup='';
const input=(name,type,placeholder,step)=>{const el=document.createElement('input');el.name=name;el.type=type;el.placeholder=placeholder||'';el.required=true;if(type==='number'){el.min='0';el.step=step||'any';}return el;};
const money=n=>new Intl.NumberFormat('fr-FR',{maximumFractionDigits:2}).format(n)+' FCFA';
const calc=()=>{let ht=0;lines.querySelectorAll('tr[data-quote-line]').forEach(tr=>{const q=Number(tr.querySelector('[name$="[quantity]"]')?.value||0),p=Number(tr.querySelector('[name$="[unit_price]"]')?.value||0),sum=Math.round(q*p*100)/100;ht+=sum;tr.querySelector('[data-line-total]').textContent=money(sum);});const discount=Number(form.querySelector('[data-discount]').value||0),net=Math.max(0,ht-discount),vat=form.querySelector('[data-vat]').checked?Math.round(net*.18*100)/100:0;form.querySelector('[data-total-ht]').textContent=money(net);form.querySelector('[data-total-vat]').textContent=money(vat);form.querySelector('[data-total-ttc]').textContent=money(net+vat);};
let groupSeq=0,activeGroup=null;
const moveBlock=(header,direction)=>{
  const block=[header];let node=header.nextElementSibling;
  while(node&&!node.classList.contains('btp-group-heading')){block.push(node);node=node.nextElementSibling;}
  if(direction==='up'){
    let previous=header.previousElementSibling;
    while(previous&&!previous.classList.contains('btp-group-heading'))previous=previous.previousElementSibling;
    if(!previous)return;
    block.forEach(item=>lines.insertBefore(item,previous));
  }else{
    if(!node)return;
    let after=node.nextElementSibling;
    while(after&&!after.classList.contains('btp-group-heading'))after=after.nextElementSibling;
    block.forEach(item=>lines.insertBefore(item,after));
  }
};
const addGroup=()=>{
  const groupId=++groupSeq,tr=document.createElement('tr');tr.className='btp-group-heading';tr.dataset.groupId=String(groupId);
  const td=document.createElement('td');td.colSpan=7;
  const bar=document.createElement('div');bar.className='btp-group-bar';
  const field=document.createElement('input');field.type='text';field.maxLength=180;field.placeholder='Nom du groupe (ex. Gros œuvre, Électricité…)';field.className='btp-group-title';field.setAttribute('aria-label','Nom du groupe');
  const button=(label,fn)=>{const el=document.createElement('button');el.type='button';el.textContent=label;el.addEventListener('click',fn);return el;};
  const sync=()=>{const name=field.value.trim();let node=tr.nextElementSibling;while(node&&!node.classList.contains('btp-group-heading')){const g=node.querySelector('[name$="[group_name]"]');if(g)g.value=name;node=node.nextElementSibling;}};
  field.addEventListener('input',sync);
  bar.append(field,button('↑',()=>moveBlock(tr,'up')),button('↓',()=>moveBlock(tr,'down')),button('Vider',()=>{field.value='';sync();field.focus();}),button('Supprimer',()=>{let node=tr.nextElementSibling;while(node&&!node.classList.contains('btp-group-heading')){const g=node.querySelector('[name$="[group_name]"]');if(g)g.value='';node=node.nextElementSibling;}tr.remove();if(activeGroup===tr)activeGroup=null;calc();}));
  td.appendChild(bar);tr.appendChild(td);lines.appendChild(tr);activeGroup=tr;field.focus();
};
const addLine=()=>{
  const i=lineId++,tr=document.createElement('tr');tr.dataset.quoteLine='';
  const groupTd=document.createElement('td'),group=input('lines['+i+'][group_name]','text','Sans groupe');group.required=false;group.readOnly=true;group.value=activeGroup?.querySelector('.btp-group-title')?.value.trim()||'';groupTd.appendChild(group);tr.appendChild(groupTd);
  const desc=document.createElement('td');desc.appendChild(input('lines['+i+'][description]','text','Désignation'));tr.appendChild(desc);
  const unitTd=document.createElement('td'),select=document.createElement('select');select.name='lines['+i+'][unit]';select.required=true;select.dataset.selectSearch='';select.dataset.placeholder='Rechercher une unité';const blank=document.createElement('option');blank.value='';blank.textContent='Choisir';select.appendChild(blank);units.forEach(u=>{const opt=document.createElement('option');opt.value=u.code;opt.textContent=u.label+' ('+u.code+')';select.appendChild(opt);});unitTd.appendChild(select);tr.appendChild(unitTd);
  for(const [field,placeholder,step] of [['quantity','1','.001'],['unit_price','0','.01']]){const td=document.createElement('td');td.appendChild(input('lines['+i+']['+field+']','number',placeholder,step));tr.appendChild(td);}
  const total=document.createElement('td');total.dataset.lineTotal='';total.textContent=money(0);tr.appendChild(total);
  const td=document.createElement('td'),remove=document.createElement('button');remove.type='button';remove.textContent='×';remove.setAttribute('aria-label','Supprimer la ligne');remove.addEventListener('click',()=>{tr.remove();calc();});td.appendChild(remove);tr.appendChild(td);
  lines.appendChild(tr);
  if(window.FineaFormComponents?.enhanceSelect)window.FineaFormComponents.enhanceSelect(select);
};
const issuedField=form.querySelector('[name=issue_date]'),durationField=form.querySelector('[name=validity_days]'),endField=form.querySelector('[name=validity_date]');
const parseDate=value=>{if(!/^\d{4}-\d{2}-\d{2}$/.test(value))return null;const date=new Date(value+'T00:00:00Z');return Number.isFinite(date.getTime())&&date.toISOString().slice(0,10)===value?date:null;};
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
    if(start&&end){const diff=daysBetween(start,end);durationField.value=diff>=0&&diff<=3650?String(diff):'';} else if(start&&validDays)endField.value=shiftDate(start,days); else if(end&&validDays)issuedField.value=shiftDate(end,-days);
    else if(start&&validDays)endField.value=shiftDate(start,days);
  }else{
    if(start&&validDays)endField.value=shiftDate(start,days);
    else if(end&&validDays)issuedField.value=shiftDate(end,-days);
    else if(start&&end){const diff=daysBetween(start,end);durationField.value=diff>=0&&diff<=3650?String(diff):'';}
  }
};
form.querySelector('[data-add-line]').addEventListener('click',addLine);form.querySelector('[data-add-group]').addEventListener('click',addGroup);form.addEventListener('input',e=>{calc();if(e.target===issuedField)syncValidity('issue');else if(e.target===durationField)syncValidity('duration');else if(e.target===endField)syncValidity('end');});form.addEventListener('change',e=>{calc();if(e.target===issuedField)syncValidity('issue');else if(e.target===durationField)syncValidity('duration');else if(e.target===endField)syncValidity('end');});addLine();calc();syncValidity('duration');
});})();