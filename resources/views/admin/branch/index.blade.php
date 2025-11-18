@extends('layouts.app')
@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css"/>
<style>
:root{
  --primary:#689c41;
  --accent:#2f5d20;
  --bg:#ffffff;
  --muted:#666;
  --glass: rgba(255,255,255,0.6);
  --card-radius:18px;
}

/* page layout */
body { background:var(--bg);}
.map-stage{ max-width:1200px; margin:28px auto; padding:18px; display:flex; gap:18px; align-items:start; }
.map-panel{ flex:1; background: linear-gradient(180deg,#fff,#fbfff5); border-radius:var(--card-radius); box-shadow:0 20px 60px rgba(6,16,8,0.06); padding:18px; border:1px solid #eef6ea; position:relative; overflow:hidden; }

/* large title */
.map-hero{ display:flex; align-items:center; gap:14px; margin-bottom:12px; }
.logo-badge{ width:62px; height:62px; border-radius:14px; background:linear-gradient(135deg,var(--primary),var(--accent)); display:grid; place-items:center; color:#fff; font-weight:900; font-size:20px; transform:rotate(-9deg); box-shadow:0 10px 40px rgba(104,156,65,0.18); }
.map-title{ font-size:20px; font-weight:800; color:var(--primary); margin:0; }
.map-sub{ color:var(--muted); margin:0; font-size:13px; }

/* map size */
#map { width:100%; height:360px; border-radius:12px; overflow:hidden; }

/* floating controls */
.controls-float{ position:absolute; left:18px; top:18px; display:flex; gap:8px; z-index:600; }
.ctrl-btn{ background:rgba(255,255,255,0.92); border:1px solid #e9efe4; padding:10px 12px; border-radius:10px; cursor:pointer; font-weight:700; box-shadow: 0 6px 22px rgba(8,12,6,0.06); }
.ctrl-btn.primary{ background:linear-gradient(90deg,var(--primary),#5c8a34); color:#061006; }

/* pulse marker via divIcon */
.pulse-marker { display:flex; align-items:center; justify-content:center; width:34px; height:34px; border-radius:50%; position:relative; transform:translate(-50%,-50%); }
.pulse-marker .dot {
  width:18px; height:18px; border-radius:50%; background:var(--primary); box-shadow:0 8px 20px rgba(104,156,65,0.28);
  position:relative; z-index:2;
  animation: popIn .45s cubic-bezier(.2,.9,.25,1);
}
.pulse-marker .ring {
  position:absolute; width:18px; height:18px; border-radius:50%; left:50%; top:50%; transform:translate(-50%,-50%);
  background: rgba(104,156,65,0.14);
  animation: pulse 1.8s infinite;
  z-index:1;
}
.pulse-marker .shadow {
  position:absolute; bottom:-10px; left:50%; transform:translateX(-50%); width:48px; height:12px; border-radius:50%;
  background: radial-gradient(ellipse at center, rgba(6,12,6,0.12) 0%, rgba(6,12,6,0.03) 60%, transparent 100%);
  filter:blur(2px); z-index:0; transition: transform .25s, opacity .25s;
}

/* pop / pulse keyframes */
@keyframes pulse {
  0%{ transform:scale(.7); opacity:.7 }
  70%{ transform:scale(2.2); opacity:0 }
  100%{ opacity:0 }
}
@keyframes popIn {
  0%{ transform:scale(.2) }
  70%{ transform:scale(1.06) }
  100%{ transform:scale(1) }
}

/* sidebar minimal (optional) */
.side-panel{ width:360px; background:linear-gradient(180deg,#fff,#fbfff5); border-radius:var(--card-radius); padding:18px; border:1px solid #eef6ea; box-shadow:0 20px 60px rgba(6,16,8,0.06); }
.branch-item{ display:flex; align-items:center; gap:12px; padding:10px; border-radius:10px; cursor:pointer; transition:transform .28s, box-shadow .28s; }
.branch-item:hover{ transform:translateY(-6px); box-shadow:0 18px 48px rgba(6,16,8,.06); }
.avatar{ width:56px; height:56px; border-radius:10px; background:linear-gradient(135deg,var(--primary),#9fd177); display:grid; place-items:center; color:#05310a; font-weight:800; }

/* autoplay badge */
.tour-badge{ display:inline-block; margin-left:10px; font-size:13px; color:#fff; background:var(--primary); padding:6px 10px; border-radius:999px; font-weight:800; box-shadow:0 8px 30px rgba(104,156,65,.18); }

/* responsive */
@media (max-width:980px){
  .map-stage{ flex-direction:column; }
  .side-panel{ width:100%; order:2; }
  #map { height:520px; }
}
</style>

<div class="map-stage container">
  <div class="map-panel">
    <div class="map-hero">
      <div class="logo-badge">BR</div>
      <div>
        <h3 class="map-title">{{ __('Our branches') }}</h3>
      </div>
      <div style="margin-left:auto; display:flex; gap:8px; align-items:center;">
        <span class="tour-badge" id="tourStatus">{{ __('Tour: Off') }}</span>
      </div>
    </div>

    <div class="controls-float">
      <button class="ctrl-btn" id="btnZoomIn">+</button>
      <button class="ctrl-btn" id="btnZoomOut">−</button>
      <button class="ctrl-btn primary" id="btnTour">{{ __('Start Tour') }}</button>
    </div>
    
    <div id="map"></div>
  </div>

  <aside class="side-panel">
    <h4 style="margin:0 0 12px;display: flex;justify-content: space-between;align-items: center;font-size: 1rem;">{{ __('Branches List') }}<button class="ctrl-btn primary" id="btnAddBranch"> <i class="fa fa-plus-circle me-2"></i>{{ __('Add Branch') }}</button></h4>
    <div id="branchList">
      {{-- العناصر تتولّد جافاسكربتياً --}}
    </div>
  </aside>
</div>

<!-- Modal: Add Branch -->
<div class="modal fade" id="addBranchModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="{{ route('admin.branches.store') }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">{{ __('Add Branch') }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        
        <div class="modal-body">
            <div class="mb-3">
                <label>{{ __('Branch Name') }}</label>
                <input type="text" name="name" class="form-control">
            </div>
            
            <div class="mb-3">
                <label>{{ __('Address') }}</label>
                <input type="text" name="address" class="form-control">
            </div>
            
            <div class="mb-3">
                <label>{{ __('Latitude') }}</label>
                <input type="text" name="lat" class="form-control">
            </div>
            
            <div class="mb-3">
                <label>{{ __('Longitude') }}</label>
                <input type="text" name="lng" class="form-control">
            </div>

            <div class="modal-footer">
              <button class="btn btn-success" type="submit">{{ ('Save') }}</button>
            </div>
        </div>
      </form>
    </div>
  </div>
</div>


<!-- Modal: Edit Branch -->
<div class="modal fade" id="editBranchModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="editBranchForm" action="{{ route('admin.branches.update') }}" method="POST">
        @csrf
        @method('PUT')
        <input type="hidden" name="id">

        <div class="modal-header">
          <h5 class="modal-title">{{ __('Update Branch') }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        
        <div class="modal-body">
          <div class="mb-3">
            <label>{{ __('Branch Name') }}</label>
            <input type="text" name="name" class="form-control">
          </div>

          <div class="mb-3">
            <label>{{ __('Address') }}</label>
            <input type="text" name="address" class="form-control">
          </div>

          <div class="mb-3">
            <label>{{ __('Latitude') }}</label>
            <input type="text" name="lat" class="form-control">
          </div>

          <div class="mb-3">
            <label>{{ __('Longitude') }}</label>
            <input type="text" name="lng" class="form-control">
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn btn-primary" type="submit">{{ __('update') }}</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Leaflet -->
<script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>

<script>
const branches = @json($branches);

// initialize map
const centerLat = branches.length ? parseFloat(branches[0].lat) : 26.0940;
const centerLng = branches.length ? parseFloat(branches[0].lng) : 34.2707;
const map = L.map('map', { zoomControl:true, attributionControl:false }).setView([centerLat, centerLng], 6);

// tiles (OpenStreetMap)
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
  maxZoom: 19,
  attribution: '&copy; OpenStreetMap contributors'
}).addTo(map);

// container for markers
const markerGroup = L.layerGroup().addTo(map);

// custom divIcon factory
function createPulseIcon() {
  const html = `<div class="pulse-marker">
                  <div class="ring"></div>
                  <div class="dot"></div>
                  <div class="shadow"></div>
                </div>`;
  return L.divIcon({
    className: '',
    html,
    iconSize: [34, 34],
    iconAnchor: [17, 17]
  });
}

// build markers and sidebar list
const markers = [];
const listEl = document.getElementById('branchList');

branches.forEach((b, idx) => {
  const lat = parseFloat(b.lat);
  const lng = parseFloat(b.lng);
  const title = b.name;
  const address = b.address || '';
    if (!b.lat || !b.lng || isNaN(parseFloat(b.lat)) || isNaN(parseFloat(b.lng))) {
        console.warn("فرع بدون إحداثيات → تم تجاهله:", b);
        return;
    }
  // marker with pulse icon
  const marker = L.marker([lat, lng], { icon: createPulseIcon(), title }).addTo(markerGroup);

  // info content (glass style)
  const html = `<div class="info">
                  <h4>${escapeHtml(title)}</h4>
                  <p>${escapeHtml(address)}</p>
                  <small style="color:#888;display:block;margin-top:6px">#{${b.id}}</small>
                </div>`;
  marker.bindPopup(html, { closeButton: true, offset: L.point(0,-10) });

  // animate bounce in on load
  marker.on('add', () => {
    const iconEl = marker.getElement();
    if(iconEl){
      iconEl.style.transform = 'translate(-50%,-80%) scale(0.3)';
      iconEl.style.opacity = '0';
      setTimeout(()=> {
        iconEl.style.transition = 'transform .6s cubic-bezier(.2,.9,.25,1), opacity .6s';
        iconEl.style.transform = 'translate(-50%,-50%) scale(1)';
        iconEl.style.opacity = '1';
      }, 80 * idx);
    }
  });

  // sidebar item
  const item = document.createElement('div');
  item.className = 'branch-item';
    item.innerHTML = `
        <div class="avatar">${shortCode(title)}</div>
        <div style="flex:1">
            <strong>${escapeHtml(title)}</strong>
            <div style="color:var(--muted); font-size:13px">${escapeHtml(address)}</div>
        </div>
        <div style="text-align:left">
            <button class="btn btn-outline-primary circleIcon" data-idx="${idx}"><img src="{{ asset('assets/icons-admin/eye.svg') }}" alt="icon" loading="lazy" /></button>
            <button class="btn btn-outline-primary circleIcon" onclick="editBranch(${b.id})"><img src="{{ asset('assets/icons-admin/edit.svg') }}" alt="edit" loading="lazy" /></button>
            <button class="btn btn-outline-danger circleIcon" onclick="deleteBranch(${b.id})" style="color:red"><img src="{{ asset('assets/icons-admin/trash.svg') }}" alt="delete" loading="lazy" /></button>
        </div>
    `;

  listEl.appendChild(item);

  // click handlers
  item.addEventListener('click', ()=> focusBranch(idx) );
  marker.getElement()?.addEventListener?.('click', ()=> marker.openPopup());

  markers.push({ marker, b });
});

// helper: fly to branch
function focusBranch(index){
  const { marker, b } = markers[index];
  const latlng = marker.getLatLng();
  map.flyTo(latlng, 13, { duration: 0.9 });
  setTimeout(()=> marker.openPopup(), 900);
  // accent shadow scale
  const el = marker.getElement();
  if(el) {
    const shadow = el.querySelector('.shadow');
    if(shadow){ shadow.style.transform = 'translateX(-50%) scale(1.3)'; shadow.style.opacity = '0.9'; setTimeout(()=>{ shadow.style.transform='translateX(-50%) scale(1)'; shadow.style.opacity='1'; }, 800); }
  }
}

// zoom controls
document.getElementById('btnZoomIn').addEventListener('click', ()=> map.zoomIn());
document.getElementById('btnZoomOut').addEventListener('click', ()=> map.zoomOut());

// small util functions
function shortCode(name){
  const parts = name.trim().split(/\s+/);
  if(parts.length === 1) return parts[0].slice(0,2).toUpperCase();
  return (parts[0][0] + (parts[1] ? parts[1][0] : '')).toUpperCase();
}

function escapeHtml(str){ return String(str||'').replace(/[&<>"']/g, (s)=> ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s])); }

// Tour autoplay (fly between markers)
let tourTimer = null;
let tourIndex = 0;
const btnTour = document.getElementById('btnTour');
const tourStatus = document.getElementById('tourStatus');

btnTour.addEventListener('click', ()=> {
  if(tourTimer){ stopTour(); } else { startTour(); }
});

function startTour(){
  tourStatus.textContent = '{{ __("Tour: On") }}';
  btnTour.textContent = 'إيقاف الجولة';
  tourIndex = 0;
  tourTimer = setInterval(()=> {
    focusBranch(tourIndex);
    tourIndex++;
    if(tourIndex >= markers.length) tourIndex = 0;
  }, 2200);
}

function stopTour(){
  tourStatus.textContent = '{{ __("Tour: Off") }}';
  btnTour.textContent = 'تشغيل الجولة';
  clearInterval(tourTimer);
  tourTimer = null;
}

// initial center/fit bounds if many branches
if(markers.length > 1){
  const group = L.featureGroup(markers.map(m=> m.marker));
  map.fitBounds(group.getBounds().pad(0.35), { animate:true, duration:0.9 });
}

// accessibility: keyboard nav through branch list (optional)
document.addEventListener('keydown', (e) => {
  if(e.key === 'ArrowRight') focusBranch((tourIndex = (tourIndex+1)%markers.length));
  if(e.key === 'ArrowLeft') focusBranch((tourIndex = (tourIndex-1+markers.length)%markers.length));
});
// زر فتح مودال الإضافة
document.getElementById('btnAddBranch').addEventListener('click', () => {
    const modal = new bootstrap.Modal(document.getElementById('addBranchModal'));
    modal.show();
});

function editBranch(id){
    fetch(`/admin/branches/${id}/edit`)
    .then(res => res.json())
    .then(data => {
        let form = document.getElementById('editBranchForm');
        form.id.value = data.id;
        form.name.value = data.name;
        form.address.value = data.address;
        form.lat.value = data.lat;
        form.lng.value = data.lng;

        const modal = new bootstrap.Modal(document.getElementById('editBranchModal'));
        modal.show();
    });
}

function deleteBranch(id){
    Swal.fire({
        title: '{{ __("Are you sure?") }}',
        text: '{{ __("You will not be able to revert this!") }}',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#689c41',
        confirmButtonText: '{{ __("Yes, delete it!") }}',
        cancelButtonText: '{{ __("Cancel") }}'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/admin/branch/${id}/destroy`, {
            method: "DELETE",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            }
            }).then(res => res.json())
            .then(data => {
                if(data.status === 'deleted'){
                    location.reload();
                }
            });
        }
    });
}

setTimeout(() => {
  map.invalidateSize();
}, 300);

</script>

@endsection
