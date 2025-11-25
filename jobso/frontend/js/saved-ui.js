import { listSaved } from './jobs-actions.js';

function setSavedCount(n){
  const el = document.getElementById('savedCount');
  if (!el) return;
  // animate pulse when count changes
  const prev = el.textContent;
  el.textContent = n;
  try {
    if (prev !== String(n)) {
      el.classList.remove('pulse');
      // trigger reflow to restart animation
      void el.offsetWidth;
      el.classList.add('pulse');
    }
  } catch (e) { /* ignore */ }
}

async function refresh(){
  try{
    const res = await listSaved();
    const count = Array.isArray(res.saved) ? res.saved.length : 0;
    setSavedCount(count);
  }catch(e){ setSavedCount(0); }
}

window.addEventListener('DOMContentLoaded', ()=>{
  refresh();
  window.addEventListener('saved-changed', (ev)=>{
    if (ev && ev.detail && typeof ev.detail.count === 'number') setSavedCount(ev.detail.count);
    else refresh();
  });
});
