<?php
declare(strict_types=1);
namespace App\View\Components;
use App\Helpers\View;

/** Reusable accessible searchable select, tag multi-select and checkbox. */
final class FormControls {
 public static function searchable(string $name,array $options,array $config=[]):string {
  $multi=(bool)($config['multiple']??false);$id='fc-'.preg_replace('/[^a-z0-9_-]/i','-',$name).'-'.substr(md5(json_encode($config)),0,6);
  $selected=array_map('strval',(array)($config['selected']??[]));$placeholder=(string)($config['placeholder']??'Rechercher et sélectionner…');
  $e=static fn($v)=>View::e((string)$v);
  $html='<div class="fc-combo" data-fc-combo data-name="'.$e($name).($multi?'[]':'').'" data-multiple="'.($multi?'1':'0').'"><div class="fc-combo-control"><div class="fc-tags" data-fc-tags></div><input id="'.$e($id).'" class="fc-combo-input" type="search" autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="'.$e($id).'-list" placeholder="'.$e($placeholder).'"><button type="button" class="fc-chevron" aria-label="Afficher les options">⌄</button></div><div class="fc-combo-list" id="'.$e($id).'-list" role="listbox" '.($multi?'aria-multiselectable="true"':'').' hidden>';
  foreach($options as $value=>$label){$v=(string)$value;$chosen=in_array($v,$selected,true);$html.='<button type="button" role="option" class="fc-option" data-value="'.$e($v).'" aria-selected="'.($chosen?'true':'false').'"><span class="fc-option-check">✓</span><span>'.$e($label).'</span></button>';}
  $html.='<p class="fc-no-results" hidden>Aucun résultat</p></div><div data-fc-hidden>';
  foreach($selected as $v)if(array_key_exists($v,$options))$html.='<input type="hidden" name="'.$e($name).($multi?'[]':'').'" value="'.$e($v).'">';
  $html.='</div></div>';
  return $html;
 }
 public static function checkbox(string $name,string $label,string $value='1',bool $checked=false):string {
  $e=static fn($v)=>View::e((string)$v);
  return '<label class="fc-check"><input type="checkbox" name="'.$e($name).'" value="'.$e($value).'" '.($checked?'checked':'').'><span class="fc-check-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m5 12 4 4L19 6"/></svg></span><span>'.$e($label).'</span></label>';
 }
 public static function assets():string {return <<<'HTML'
<style>
.fc-combo{position:relative;width:100%;min-width:0;font-weight:400}.fc-combo-control{display:flex;align-items:center;flex-wrap:wrap;gap:6px;min-height:48px;padding:6px 10px;border:1px solid #cbd5e1;border-radius:12px;background:#fff;transition:.15s}.fc-combo:focus-within .fc-combo-control{border-color:#2563eb;box-shadow:0 0 0 3px #2563eb22}.fc-combo-input{border:0!important;outline:0!important;box-shadow:none!important;flex:1 1 130px;min-width:95px;min-height:30px;background:transparent!important;padding:4px!important;width:auto!important}.fc-chevron{border:0;background:transparent;color:#475569;font-size:22px;cursor:pointer;padding:0 5px}.fc-combo-list{position:absolute;z-index:150;top:calc(100% + 5px);left:0;right:0;max-height:270px;overflow:auto;border:1px solid #cbd5e1;border-radius:12px;background:#fff;box-shadow:0 15px 35px #0f172a26;padding:6px}.fc-combo-list[hidden],.fc-no-results[hidden]{display:none}.fc-option{display:flex;align-items:center;gap:10px;width:100%;padding:10px;border:0;border-radius:8px;background:transparent;color:#0f172a;text-align:left;cursor:pointer;font:inherit}.fc-option:hover,.fc-option:focus-visible{background:#eff6ff;outline:0}.fc-option[aria-selected=true]{background:#eff6ff;color:#1d4ed8;font-weight:600}.fc-option-check{width:22px;height:22px;display:grid;place-items:center;border:2px solid #cbd5e1;border-radius:7px;color:transparent;font-size:14px}.fc-option[aria-selected=true] .fc-option-check{background:#2563eb;border-color:#2563eb;color:#fff}.fc-tag{display:inline-flex;align-items:center;gap:6px;max-width:100%;padding:4px 8px;border-radius:7px;background:#dbeafe;color:#1d4ed8;font-size:13px;font-weight:600}.fc-tag-label{overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:210px}.fc-tag-remove{border:0;background:transparent;color:inherit;font-size:17px;cursor:pointer;line-height:1}.fc-no-results{padding:12px;color:#64748b}.fc-check{display:inline-flex!important;align-items:center;gap:10px;cursor:pointer;position:relative;font-weight:600}.fc-check input{position:absolute;opacity:0;width:1px;height:1px}.fc-check-icon{display:grid;place-items:center;width:27px;height:27px;flex:none;border:2px solid #94a3b8;border-radius:8px;background:#fff;color:transparent;transition:.15s}.fc-check-icon svg{width:18px;height:18px}.fc-check input:checked+.fc-check-icon{background:#2563eb;border-color:#2563eb;color:white;box-shadow:0 3px 8px #2563eb35}.fc-check input:focus-visible+.fc-check-icon{outline:3px solid #93c5fd;outline-offset:2px}.fc-check input:disabled+.fc-check-icon{opacity:.5}
</style>
<script>
(function(){
 function init(root){if(root.dataset.fcReady)return;root.dataset.fcReady='1';
 const input=root.querySelector('.fc-combo-input'),panel=root.querySelector('.fc-combo-list'),tags=root.querySelector('[data-fc-tags]'),hidden=root.querySelector('[data-fc-hidden]'),opts=[...root.querySelectorAll('.fc-option')],multi=root.dataset.multiple==='1',name=hidden.querySelector('input')?.name||root.dataset.name||'';
 const selected=new Set([...hidden.querySelectorAll('input')].map(x=>x.value));
 function draw(){hidden.replaceChildren();tags.replaceChildren();opts.forEach(o=>{let on=selected.has(o.dataset.value);o.setAttribute('aria-selected',String(on));if(on){let h=document.createElement('input');h.type='hidden';h.name=name;h.value=o.dataset.value;hidden.append(h);let tag=document.createElement('span');tag.className='fc-tag';let label=document.createElement('span');label.className='fc-tag-label';label.textContent=o.innerText.replace(/^✓/,'').trim();tag.append(label);if(multi){let b=document.createElement('button');b.type='button';b.className='fc-tag-remove';b.textContent='×';b.setAttribute('aria-label','Retirer '+label.textContent);b.addEventListener('click',()=>{selected.delete(o.dataset.value);draw();});tag.append(b);}tags.append(tag);}});input.placeholder=selected.size?'Ajouter ou rechercher…':root.dataset.placeholder||input.getAttribute('placeholder');root.dispatchEvent(new CustomEvent('fc:change',{bubbles:true,detail:{values:[...selected]}}));}
 function filter(){let q=input.value.toLocaleLowerCase().trim(),count=0;opts.forEach(o=>{let visible=o.textContent.toLocaleLowerCase().includes(q);o.hidden=!visible;if(visible)count++;});panel.querySelector('.fc-no-results').hidden=count!==0;}
 function open(){panel.hidden=false;input.setAttribute('aria-expanded','true');filter();}
 function close(){panel.hidden=true;input.setAttribute('aria-expanded','false');input.value='';filter();}
 opts.forEach(o=>o.addEventListener('click',()=>{if(!multi)selected.clear();if(selected.has(o.dataset.value))selected.delete(o.dataset.value);else selected.add(o.dataset.value);draw();if(!multi)close();else{input.value='';filter();input.focus();}}));
 input.addEventListener('focus',open);input.addEventListener('input',open);input.addEventListener('keydown',e=>{if(e.key==='Escape')close();if(e.key==='Enter'&&!panel.hidden){let first=opts.find(o=>!o.hidden);if(first){e.preventDefault();first.click();}}});
 root.querySelector('.fc-chevron').addEventListener('click',()=>{if(panel.hidden){open();input.focus();}else close();});
 document.addEventListener('click',e=>{if(!root.contains(e.target))close();});
 root.dataset.name=name;root.dataset.placeholder=input.placeholder;draw();
 }
 document.addEventListener('DOMContentLoaded',()=>document.querySelectorAll('[data-fc-combo]').forEach(init));
})();
</script>
HTML;
 }
}
