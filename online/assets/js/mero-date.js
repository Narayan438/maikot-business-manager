(()=>{
  const pad=n=>String(n).padStart(2,'0');
  const fmt=o=>o.year+'-'+pad(o.month)+'-'+pad(o.date);
  const valid=s=>/^\d{4}-\d{2}-\d{2}$/.test(s||'');
  function convert(value,mode){
    if(!valid(value)||typeof DateConverter==='undefined') return '';
    try{return fmt(mode==='BS'?new DateConverter(value).toAd():new DateConverter(value).toBs());}catch(e){return '';}
  }
  function todayAD(){const d=new Date();return d.getFullYear()+'-'+pad(d.getMonth()+1)+'-'+pad(d.getDate());}
  function init(w){
    const hidden=w.querySelector('input[data-dual-hidden]'), bs=w.querySelector('.mk-bs'), ad=w.querySelector('.mk-ad');
    const b1=w.querySelector('[data-mode="BS"]'), b2=w.querySelector('[data-mode="AD"]');
    let mode=(w.dataset.mode||'BS').toUpperCase();
    let raw=hidden.value||w.dataset.value||'';
    let bsVal='',adVal='';
    if(raw && +raw.slice(0,4)>=2070){bsVal=raw;adVal=convert(raw,'BS');}
    else if(raw){adVal=raw;bsVal=convert(raw,'AD');}
    if(!raw){adVal=todayAD();bsVal=convert(adVal,'AD');}
    bs.value=bsVal;ad.value=adVal;
    function show(m){mode=m;b1.classList.toggle('active',m==='BS');b2.classList.toggle('active',m==='AD');bs.hidden=m!=='BS';ad.hidden=m!=='AD';hidden.value=m==='BS'?bs.value:convert(ad.value,'AD');}
    bs.addEventListener('change',()=>{const x=convert(bs.value,'BS');if(x){ad.value=x;hidden.value=bs.value}else alert('Invalid/unsupported BS date. Use YYYY-MM-DD.');});
    ad.addEventListener('change',()=>{const x=convert(ad.value,'AD');if(x){bs.value=x;hidden.value=x}else alert('Invalid/unsupported AD date.');});
    b1.onclick=()=>show('BS');b2.onclick=()=>show('AD');show(mode);
  }
  function decorate(){
    document.querySelectorAll('[data-mk-bs-date]').forEach(el=>{
      const bs=el.dataset.mkBsDate||el.textContent.trim(), ad=convert(bs,'BS');
      if(ad) el.innerHTML='<span class="mk-date-primary">'+bs+' BS</span><br><span class="mk-date-secondary">'+ad+' AD</span>';
    });
  }
  window.MeroDate={convert,init,decorate};
  document.addEventListener('DOMContentLoaded',()=>{document.querySelectorAll('.mk-dual-date').forEach(init);decorate();});
})();