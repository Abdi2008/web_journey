// Client-side auth UI for JOBSO frontend
function escapeHtml(s){
  return String(s)
    .replace(/&/g,'&amp;')
    .replace(/</g,'&lt;')
    .replace(/>/g,'&gt;')
    .replace(/"/g,'&quot;')
    .replace(/'/g,'&#39;');
}

async function renderAuth(){
  const el = document.getElementById('authArea');
  const nav = document.querySelector('.site-header nav');
  if (el) el.innerHTML = '…';
  try{
    const res = await fetch('/JOBSO/api/me.php', {credentials:'same-origin'});
    const ct = res.headers.get('content-type') || '';
    let json = null;
    if (ct.includes('application/json')) {
      try {
        json = await res.json();
      } catch (e) {
        console.error('Failed to parse JSON from /api/me.php', e);
        json = null;
      }
    } else {
      // Non-JSON response (could be HTML error). Read text for diagnostics and continue.
      const txt = await res.text();
      console.warn('Non-JSON response from /api/me.php:', txt);
      json = null;
    }
    const loggedIn = res.ok && json && json.user;
    const role = json && json.user && json.user.role ? json.user.role : 'candidate';

    // Update nav links visibility and set Dashboard href based on role
    if (nav) {
      const links = Array.from(nav.querySelectorAll('a'));
      links.forEach(a=>{
        const href = a.getAttribute('href') || '';
        // show Post Job only for employers/admins
        if (href.includes('post_job.html')) {
          a.style.display = (loggedIn && (role === 'employer' || role === 'admin')) ? '' : 'none';
        }
        // show dashboard only when logged in
        if (href.includes('dashboard')) {
          a.style.display = loggedIn ? '' : 'none';
          // Update dashboard link to role-specific page
          if (loggedIn) {
            if (role === 'admin') {
              a.href = '/JOBSO/frontend/admin.html';
            } else if (role === 'employer') {
              a.href = '/JOBSO/frontend/dashboard_employer.html';
            } else {
              a.href = '/JOBSO/frontend/dashboard_candidate.html';
            }
          }
        }
        // hide login/register when logged in
        if (href.includes('login.html') || href.includes('register.html')) {
          a.style.display = loggedIn ? 'none' : '';
        }
      });
    }

    if (el) {
      if (loggedIn) {
        const name = escapeHtml(json.user.name || json.user.email || 'User');
        el.innerHTML = `Logged in as <strong>${name}</strong> (${escapeHtml(role)}) · <a href="#" id="logoutLink">Logout</a>`;
        const logout = document.getElementById('logoutLink');
        if (logout) logout.addEventListener('click', async (e)=>{
          e.preventDefault();
          await fetch('/JOBSO/api/logout.php', {method:'POST', credentials:'same-origin'}).catch(()=>{});
          location.reload();
        });
      } else {
        el.innerHTML = `<a href="/JOBSO/frontend/login.html">Login</a> · <a href="/JOBSO/frontend/register.html">Register</a>`;
      }
    }
  }catch(err){
    console.error(err);
    if (el) el.innerHTML = `<a href="/JOBSO/frontend/login.html">Login</a>`;
  }
}

renderAuth();

export { renderAuth };
