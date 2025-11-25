// categories.js — compute rough job counts per category and populate the categories grid
import { apiFetch } from './app.js';

async function populateCategories() {
  const container = document.getElementById('categories');
  if (!container) return;
  const cards = Array.from(container.querySelectorAll('.category'));
  // naive mapping of category keys to keywords to search for
  const mapping = {
    engineering: ['engineer','engineering','developer','developer','software','backend','frontend'],
    design: ['design','designer','ux','ui','graphic','product design'],
    product: ['product','pm','product manager','product-management'],
    marketing: ['marketing','content','growth','seo','smm','social'],
    sales: ['sales','account','business development','bdm','bd'],
    devops: ['devops','infrastructure','site reliability','sre','platform']
  };

  try {
    const res = await fetch('/JOBSO/api/jobs.php',{credentials:'same-origin'});
    const data = await res.json();
    if (!res.ok || !Array.isArray(data.jobs)) return;
    const jobs = data.jobs;
    // lowercase searchable text
    const texts = jobs.map(j => ((j.title||'') + ' ' + (j.description||'') + ' ' + (j.company||'')).toLowerCase());
    for (const c of cards) {
      const key = c.dataset.key;
      const keywords = mapping[key] || [key];
      let count = 0;
      for (const t of texts) {
        for (const kw of keywords) {
          if (t.includes(kw)) { count++; break; }
        }
      }
      const countEl = c.querySelector('.cat-count');
      if (countEl) countEl.textContent = `${count} jobs`;
    }
  } catch (e) {
    console.error('categories populate error', e);
  }
}

window.addEventListener('DOMContentLoaded', () => {
  // populate counts after DOM ready
  populateCategories();
});

export { populateCategories };
