document.addEventListener("DOMContentLoaded", function () {
  let usuarioIDSeleccionado = null;

  const modal = document.getElementById("modal-confirma");
  const bootstrapModal = new bootstrap.Modal(modal);

  modal.addEventListener("show.bs.modal", function (event) {
    const button = event.relatedTarget;
    usuarioIDSeleccionado = button.getAttribute("data-id");
    const nombre = button.getAttribute("data-nombre");

    document.getElementById("nombreUsuario").textContent = nombre;
  });

  document
    .getElementById("btnConfirmarReactivacion")
    .addEventListener("click", function (e) {
      e.preventDefault();
      if (!usuarioIDSeleccionado) return;

      const url = `${baseUrl}admin/usuarios/reactivar/${usuarioIDSeleccionado}`;

      fetch(url, {
        method: "POST",
        headers: {
          "X-Requested-With": "XMLHttpRequest",
        },
      })
        .then((response) => response.json())
        .then((data) => {
          console.log("Respuesta JSON:", data);
          if (data.success) {
            const fila = document.getElementById(
              `usuario-${usuarioIDSeleccionado}`
            );
            if (fila) fila.remove();

            // Cierre del modal
            const modalElement = document.getElementById("modal-confirma");
            modalElement.classList.remove("show");
            modalElement.style.display = "none";
            document.body.classList.remove("modal-open");
            document.body.style = "";
            const backdrop = document.querySelector(".modal-backdrop");
            if (backdrop) backdrop.remove();

            Swal.fire({
              icon: "success",
              title: "¡Usuario reactivado!",
              text: "El usuario fue reactivado correctamente.",
              timer: 2000,
              showConfirmButton: false,
            }).then(() => {
                window.location.href = `${baseUrl}admin/usuarios`; 
            });
          } else {
            Swal.fire({
              icon: "error",
              title: "Error",
              text: data.error || "No se pudo reactivar el usuario.",
            });
          }
        });
    });
});
