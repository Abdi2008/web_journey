import { apiFetch } from './app.js';

// Client helper for job actions: save, unsave, list, apply

async function saveJob(jobId){
  if (!jobId) throw { error: 'Missing jobId' };
  const res = await apiFetch('/save_job.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ job_id: jobId }) });
  // dispatch event with new count
  try{
    const list = await listSaved();
    const count = Array.isArray(list.saved) ? list.saved.length : 0;
    window.dispatchEvent(new CustomEvent('saved-changed',{detail:{count}}));
  }catch(e){/* ignore */}
  return res;
}

async function unsaveJob(jobId){
  if (!jobId) throw { error: 'Missing jobId' };
  const res = await apiFetch('/save_job.php', { method: 'DELETE', headers: {'Content-Type':'application/json'}, body: JSON.stringify({ job_id: jobId }) });
  try{
    const list = await listSaved();
    const count = Array.isArray(list.saved) ? list.saved.length : 0;
    window.dispatchEvent(new CustomEvent('saved-changed',{detail:{count}}));
  }catch(e){/* ignore */}
  return res;
}

async function listSaved(){
  return apiFetch('/save_job.php');
}

// applyData can be a FormData (multipart) or an object { job_id, cover_text }
async function applyJob(applyData){
  if (applyData instanceof FormData){
    const res = await fetch('/JOBSO/api/apply_job.php', { method: 'POST', credentials: 'same-origin', body: applyData });
    const ct = res.headers.get('content-type') || '';
    if (ct.includes('application/json')){
      const json = await res.json();
      if (!res.ok) throw json;
      return json;
    }
    const text = await res.text();
    let err = { error: text };
    try { err = JSON.parse(text); } catch(e){}
    if (!res.ok) throw err;
    return err;
  } else {
    // JSON body
    return apiFetch('/apply_job.php', { method: 'POST', headers: {'Content-Type':'application/json'}, body: JSON.stringify(applyData) });
  }
}

export { saveJob, unsaveJob, listSaved, applyJob };
