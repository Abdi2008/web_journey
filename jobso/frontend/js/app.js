// Simple frontend helper for JOBSO
const API_BASE = '/JOBSO/api';

async function apiFetch(path, opts={}){
  const url = path.startsWith('/') ? API_BASE + path : API_BASE + '/' + path;
  const res = await fetch(url, Object.assign({credentials:'same-origin'}, opts));
  const ct = res.headers.get('content-type') || '';
  if (ct.includes('application/json')) {
    const json = await res.json();
    if (!res.ok) throw json;
    return json;
  }
  // Non-JSON response: return text as error
  const text = await res.text();
  let err = { error: text };
  try { err = JSON.parse(text); } catch(e) {}
  if (!res.ok) throw err; // if OK but not JSON, return as text-wrapped object
  return err;
}

function el(tag, cls=''){
  const e = document.createElement(tag);
  if (cls) e.className = cls;
  return e;
}

function createJobCard(j){
  const card = el('div','job-card');
  // assign a tint variant based on company name for consistent coloring
  try {
    const name = String(j.company || j.title || '');
    let sum = 0;
    for (let i=0;i<name.length;i++) sum += name.charCodeAt(i);
    const variant = (sum % 4) + 1; // 1..4
    card.classList.add('tint-' + variant);
  } catch(e) { /* ignore */ }
  const logoImg = document.createElement('img');
  logoImg.className = 'logo-img';
  if (j.company_logo) {
    // company_logo is stored relative to project root (e.g. assets/company_logos/abc.svg)
    logoImg.src = '/' + j.company_logo.replace(/^\/*/,'');
  } else {
    const slug = (j.company || 'company').toLowerCase().replace(/[^a-z0-9]+/g,'-');
    logoImg.src = `/JOBSO/assets/companies/${slug}.svg`;
  }
  // on error hide image and fallback to badge; on load hide the badge
  logoImg.onerror = function(){ this.style.display='none'; };
  logoImg.onload = function(){
    const b = card.querySelector('.company-badge');
    if (b) b.style.display = 'none';
  };
  card.appendChild(logoImg);
  const badge = el('div','company-badge'); badge.textContent = j.company || 'Company';
  // if logo loads, visually prioritize it; otherwise badge remains visible
  card.appendChild(badge);
  const content = el('div','content');
  const a = el('a'); a.href = `/JOBSO/frontend/view_job.html?id=${j.id}`; a.textContent = j.title; a.className='job-title';
  content.appendChild(a);
  const meta = el('div','meta'); meta.textContent = `${j.location || ''} · posted by ${j.poster || 'Unknown'}`;
  content.appendChild(meta);
  const desc = el('div'); desc.className='job-desc'; desc.innerHTML = (j.description || '').slice(0,300).replace(/\n/g,'<br>') + (j.description && j.description.length>300 ? '…' : '');
  content.appendChild(desc);
  card.appendChild(content);
  // actions row: Save and View
  const actions = el('div','job-actions');
  const view = el('a','button'); view.href = `/JOBSO/frontend/view_job.html?id=${j.id}`; view.textContent = 'View'; view.style.marginRight='8px';
  actions.appendChild(view);
  // Apply button (quick link to job detail/apply form)
  const apply = el('a','button apply-btn'); apply.href = `/JOBSO/frontend/view_job.html?id=${j.id}`; apply.textContent = 'Apply'; apply.style.marginRight = '8px';
  actions.appendChild(apply);
  const saveBtn = el('button'); saveBtn.type='button'; saveBtn.className='save-btn'; saveBtn.textContent = 'Save';
  // toggle save/unsave using jobs-actions helper dynamically
  saveBtn.addEventListener('click', async (ev)=>{
    ev.preventDefault();
    try{
      const mod = await import('./jobs-actions.js');
      if (saveBtn.dataset.saved === '1'){
        await mod.unsaveJob(j.id);
        saveBtn.dataset.saved = '0';
        saveBtn.textContent = 'Save';
      } else {
        await mod.saveJob(j.id);
        saveBtn.dataset.saved = '1';
        saveBtn.textContent = 'Saved';
      }
    }catch(err){
      console.error(err);
      alert((err && err.error) ? err.error : 'Save failed');
    }
  });
  actions.appendChild(saveBtn);
  card.appendChild(actions);
  return card;
}

async function loadJobs(container, trendingContainer=null){
  if (!container) return;
  container.innerHTML = 'Loading…';
  if (trendingContainer) trendingContainer.innerHTML = 'Loading…';
  try{
    const res = await fetch('/JOBSO/api/jobs.php',{credentials:'same-origin'});
    const data = await res.json();
    if (!res.ok) {
      container.innerHTML = `<p>${data.error||'Error'}</p>`; return;
    }
    const jobs = data.jobs || [];
    container.innerHTML = '';
    if (!jobs.length) { container.innerHTML = '<p>No jobs yet.</p>'; if (trendingContainer) trendingContainer.innerHTML=''; return }
    // job cards grid
    // try to fetch saved jobs to mark saved state
    let savedSet = new Set();
    try{
      const sv = await apiFetch('/save_job.php');
      if (sv && Array.isArray(sv.saved)) savedSet = new Set(sv.saved.map(s=>s.job_id));
    }catch(e){ /* ignore if not logged in or error */ }
    for (const j of jobs){
      const card = createJobCard(j);
      // mark saved if present
      if (savedSet.has(j.id)){
        const btn = card.querySelector('.save-btn'); if (btn) { btn.textContent='Saved'; btn.dataset.saved='1' }
      }
      container.appendChild(card);
    }
    // trending: top 5 most recent
    if (trendingContainer){
      trendingContainer.innerHTML = '';
      const top = jobs.slice(0,5);
      for (const t of top){
        const ti = el('div','trending-item');
        ti.innerHTML = `<a href='/JOBSO/frontend/view_job.html?id=${t.id}' style='font-weight:600;color:var(--accent)'>${t.title}</a><div style='color:#666;font-size:13px'>${t.company} · ${t.location || ''}</div>`;
        trendingContainer.appendChild(ti);
      }
    }
  }catch(err){
    container.innerHTML = '<p>Error loading jobs.</p>';
    if (trendingContainer) trendingContainer.innerHTML = '';
    console.error(err);
  }
}

async function submitForm(form, path){
  // If the form contains files, send as multipart/form-data using FormData
  const fd = new FormData(form);
  const hasFile = Array.from(form.querySelectorAll('input[type=file]')).some(i=>i.files && i.files.length>0);
  if (hasFile) {
    const url = path.startsWith('/') ? API_BASE + path : API_BASE + '/' + path;
    const res = await fetch(url, {method:'POST', credentials:'same-origin', body: fd});
    const ct = res.headers.get('content-type') || '';
    if (ct.includes('application/json')) {
      const json = await res.json();
      if (!res.ok) throw json;
      return json;
    }
    const text = await res.text();
    let err = { error: text };
    try { err = JSON.parse(text); } catch(e) {}
    if (!res.ok) throw err;
    return err;
  }
  const obj = {};
  for (const [k,v] of fd.entries()) obj[k]=v;
  try{
    const res = await apiFetch(path, {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(obj)});
    return res;
  }catch(e){ throw e }
}

export { loadJobs, submitForm, apiFetch };
