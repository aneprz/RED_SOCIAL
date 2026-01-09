document.addEventListener("DOMContentLoaded", () => {
  const openBtn = document.getElementById("abrirCrear");
  const modalRoot = document.getElementById("modal");
  const overlay = document.getElementById("modalOverlay");
  const closeBtn = document.getElementById("closeModal");
  const content = modalRoot ? modalRoot.querySelector(".modal-content") : null;

  const fileInput = document.getElementById("fileInput");
  const previewContainer = document.getElementById("previewContainer");
  const mediaWrapper = document.getElementById("mediaWrapper");
  const formFields = document.getElementById("formFields");
  const tagsList = document.getElementById("tagsList");
  const hiddenTagInputs = document.getElementById("hiddenTagInputs");
  const manualTagInput = document.getElementById("manualTagInput");
  const addManualTagBtn = document.getElementById("addManualTag");
  const cancelUploadBtn = document.getElementById("cancelUpload");
  const postForm = document.getElementById("postForm");

  if (!openBtn || !modalRoot) return;

  let tags = []; // {id, name, x, y}
  let tagCounter = 0;

  /* Abrir / cerrar modal */
  openBtn.addEventListener("click", (e) => { e.preventDefault(); openModal(); });
  function openModal(){
    modalRoot.style.display = "flex";
    modalRoot.setAttribute("aria-hidden","false");
    content?.focus();
    document.body.style.overflow = "hidden";
  }
  function closeModal(){
    modalRoot.style.display = "none";
    modalRoot.setAttribute("aria-hidden","true");
    document.body.style.overflow = "";
    resetAll();
    openBtn.focus?.();
  }
  closeBtn?.addEventListener("click", closeModal);
  overlay?.addEventListener("click", closeModal);
  document.addEventListener("keydown", (e)=>{ if(e.key==="Escape" && modalRoot.style.display==="flex") closeModal(); });
  cancelUploadBtn?.addEventListener("click", closeModal);

  /* Selección de archivo -> previsualización + mostrar formulario */
  fileInput.addEventListener("change", (e) => {
    const files = e.target.files;
    if (!files || files.length === 0) return;
    const file = files[0];
    showPreview(file);

    // Mostrar formulario y botones
    formFields.style.display = "flex";
    formFields.style.flexDirection = "column";
    previewContainer.style.display = "block";
  });

  function clearMediaWrapper(){ mediaWrapper.innerHTML = ''; }

  function showPreview(file){
    clearMediaWrapper();
    tags = []; tagCounter = 0; renderTags();

    if (file.type.startsWith("image/")) {
      const img = document.createElement("img");
      img.alt = file.name;
      img.draggable = false;
      mediaWrapper.appendChild(img);
      const reader = new FileReader();
      reader.onload = (ev) => img.src = ev.target.result;
      reader.readAsDataURL(file);

    } else if (file.type.startsWith("video/")) {
      const video = document.createElement("video");
      video.controls = true;
      video.playsInline = true;
      mediaWrapper.appendChild(video);
      const reader = new FileReader();
      reader.onload = (ev) => { video.src = ev.target.result; };
      reader.readAsDataURL(file);

    } else {
      mediaWrapper.textContent = "Tipo de archivo no soportado.";
    }
  }

  /* Añadir etiqueta manual (sin coords) */
  addManualTagBtn.addEventListener("click", () => {
    let name = (manualTagInput.value||'').trim();
    if (!name) return;
    name = name.replace(/\s+/g,'');
    addTag(name.startsWith('@') ? name : '@' + name, null, null);
    manualTagInput.value = '';
  });

  function addTag(name, x, y){
    const id = ++tagCounter;
    tags.push({ id, name, x: x===null?null: Number(x), y: y===null?null: Number(y) });
    renderTags();
  }

  function removeTag(id){
    tags = tags.filter(t => t.id !== id);
    renderTags();
  }

  /* Render lista visible y hidden inputs para PHP */
  function renderTags(){
    tagsList.innerHTML = '';
    hiddenTagInputs.innerHTML = '';

    if (tags.length === 0) {
      const placeholder = document.createElement('div');
      placeholder.className = 'tag-row';
      placeholder.textContent = 'No hay etiquetas añadidas.';
      tagsList.appendChild(placeholder);
    }

    tags.forEach(t => {
      const row = document.createElement('div');
      row.className = 'tag-row';
      row.innerHTML = `<strong>${t.name}</strong> ${t.x!==null ? `— ${Math.round(t.x*100)}%, ${Math.round(t.y*100)}%` : ''}`;
      const rem = document.createElement('span');
      rem.className = 'remove';
      rem.textContent = 'Eliminar';
      rem.addEventListener('click', () => removeTag(t.id));
      row.appendChild(rem);
      tagsList.appendChild(row);

      // inputs ocultos para PHP
      const inName = document.createElement('input');
      inName.type = 'hidden'; inName.name = 'tags_names[]'; inName.value = t.name;
      hiddenTagInputs.appendChild(inName);

      const inX = document.createElement('input');
      inX.type = 'hidden'; inX.name = 'tags_x[]'; inX.value = t.x===null ? '' : t.x;
      hiddenTagInputs.appendChild(inX);

      const inY = document.createElement('input');
      inY.type = 'hidden'; inY.name = 'tags_y[]'; inY.value = t.y===null ? '' : t.y;
      hiddenTagInputs.appendChild(inY);

      if (t.x !== null && t.y !== null){
        placeMarker(t);
      }
    });
  }

  function placeMarker(tag){
    const existing = mediaWrapper.querySelectorAll(`.tag-marker[data-id="${tag.id}"]`);
    existing.forEach(n=>n.remove());

    const marker = document.createElement('button');
    marker.type = 'button';
    marker.className = 'tag-marker';
    marker.dataset.id = tag.id;
    marker.title = tag.name;
    marker.textContent = '+';
    marker.style.left = (tag.x * 100) + '%';
    marker.style.top = (tag.y * 100) + '%';
    marker.addEventListener('click', (ev)=>{
      ev.stopPropagation();
      if (confirm(`Eliminar etiqueta ${tag.name}?`)) removeTag(tag.id);
    });
    mediaWrapper.appendChild(marker);
  }

  /* Reset cuando se cierra */
  function resetAll(){
    fileInput.value = '';
    clearPreview();
    formFields.style.display = 'none';
    previewContainer.style.display = 'none';
    tags = []; tagCounter = 0; renderTags();
    postForm.reset();
  }

  function clearPreview(){
    mediaWrapper.innerHTML = '';
  }

  /* Validación antes de enviar */
  postForm.addEventListener('submit', (e) => {
    if (!fileInput.files || fileInput.files.length===0) {
      e.preventDefault();
      alert('Selecciona un archivo antes de subir.');
      return false;
    }

    const caption = document.getElementById('caption').value.trim();
    if(caption.length > 2200){
      e.preventDefault();
      alert('El pie de foto no puede exceder 2200 caracteres.');
      return false;
    }

    if(tags.some(t => !t.name)){
      e.preventDefault();
      alert('Hay etiquetas sin nombre.');
      return false;
    }
  });
    // Enviar formulario al hacer clic en "Subir Archivo"
  const btnSubmitFile = document.getElementById("btnSubmitFile");
  btnSubmitFile.addEventListener("click", () => {
    postForm.requestSubmit(); // dispara el submit del formulario
  });

});
