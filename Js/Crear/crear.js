document.addEventListener("DOMContentLoaded", () => {
  // id que tú usas en el sidebar: "abrirCrear"
  const openBtn = document.getElementById("abrirCrear");
  const modalRoot = document.getElementById("modal");
  const overlay = document.getElementById("modalOverlay");
  const closeBtn = document.getElementById("closeModal");
  const content = modalRoot ? modalRoot.querySelector(".modal-content") : null;
  const fileInput = document.getElementById("fileInput");
  const fileList = document.getElementById("fileList");

  if (!openBtn || !modalRoot) return;

  // Abrir modal al clicar el enlace del navbar
  openBtn.addEventListener("click", (e) => {
    e.preventDefault();
    openModal();
  });

  function openModal() {
    modalRoot.style.display = "flex";
    modalRoot.setAttribute("aria-hidden", "false");
    if (content) content.focus();
    document.body.style.overflow = "hidden"; // bloquear scroll del body
  }

  function closeModal() {
    modalRoot.style.display = "none";
    modalRoot.setAttribute("aria-hidden", "true");
    document.body.style.overflow = "";
    clearFileList();
  }

  // Cerrar con el botón X y clic en overlay
  if (closeBtn) closeBtn.addEventListener("click", closeModal);
  if (overlay) overlay.addEventListener("click", closeModal);

  // Cerrar con Escape
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && modalRoot.style.display === "flex") {
      closeModal();
    }
  });

  // Manejo de selección con el input
  if (fileInput) {
    fileInput.addEventListener("change", (e) => {
      handleFiles(e.target.files);
    });
  }

  // Manejar archivos (solo previsualización local)
  function handleFiles(files) {
    if (!fileList) return;
    const arr = Array.from(files || []);
    if (arr.length === 0) return;
    clearFileList();
    arr.forEach(file => {
      const item = document.createElement("div");
      item.className = "file-item";

      if (file.type.startsWith("image/")) {
        const img = document.createElement("img");
        img.style.maxWidth = "100%";
        img.style.maxHeight = "100%";
        img.alt = file.name;
        item.appendChild(img);

        const reader = new FileReader();
        reader.onload = (ev) => img.src = ev.target.result;
        reader.readAsDataURL(file);
      } else {
        item.textContent = file.name;
      }

      fileList.appendChild(item);
    });

    // Aquí puedes enviar archivos al servidor con fetch + FormData si quieres
  }

  function clearFileList() {
    if (!fileList) return;
    fileList.innerHTML = "";
    if (fileInput) fileInput.value = "";
  }
});
