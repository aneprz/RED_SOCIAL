function textoValidarContraseña() {
    document.getElementById("requisitosContraseña").style.display="block";
}

//VALIDACIÓN DE CONTRASEÑA
let contraseña=document.getElementById("contraseña");
let repetirContraseña = document.getElementById("repetirContraseña");
let validado=0;

//Validar que la contraseña tenga mínimo 8 carácteres
function validarLongitudContraseña() {
    let resultadoLongitud=document.getElementById("longitud");
    let textoOriginal=resultadoLongitud.innerHTML;

    contraseña.addEventListener("input", ()=>{
        let esValida=contraseña.value.length>=8;

        if (esValida) {
            resultadoLongitud.style.color="green";
            resultadoLongitud.innerHTML=`<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"><path fill="currentColor" d="m10.6 16.6l7.05-7.05l-1.4-1.4l-5.65 5.65l-2.85-2.85l-1.4 1.4zM12 22q-2.075 0-3.9-.788t-3.175-2.137T2.788 15.9T2 12t.788-3.9t2.137-3.175T8.1 2.788T12 2t3.9.788t3.175 2.137T21.213 8.1T22 12t-.788 3.9t-2.137 3.175t-3.175 2.138T12 22m0-2q3.35 0 5.675-2.325T20 12t-2.325-5.675T12 4T6.325 6.325T4 12t2.325 5.675T12 20m0-8"/></svg>
            Mínimo 8 carácteres.`;
        } else{
            resultadoLongitud.style.color="";
            resultadoLongitud.innerHTML=textoOriginal;
        }
    })
    habilitarBoton();
}

//Validar que la contraseña tenga al menos una mayúscula
function validarUnaMayuscula() {
    let resultadoMayuscula=document.getElementById("mayuscula");
    let textoOriginal=resultadoMayuscula.innerHTML;

    contraseña.addEventListener("input",()=>{
        let esValida=/[A-Z]/.test(contraseña.value);

        if (esValida) {
            resultadoMayuscula.style.color="green";
            resultadoMayuscula.innerHTML=`<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"><path fill="currentColor" d="m10.6 16.6l7.05-7.05l-1.4-1.4l-5.65 5.65l-2.85-2.85l-1.4 1.4zM12 22q-2.075 0-3.9-.788t-3.175-2.137T2.788 15.9T2 12t.788-3.9t2.137-3.175T8.1 2.788T12 2t3.9.788t3.175 2.137T21.213 8.1T22 12t-.788 3.9t-2.137 3.175t-3.175 2.138T12 22m0-2q3.35 0 5.675-2.325T20 12t-2.325-5.675T12 4T6.325 6.325T4 12t2.325 5.675T12 20m0-8"/></svg>
            Al menos una mayúscula.`;
        }else{
            resultadoMayuscula.style.color="";
            resultadoMayuscula.innerHTML=textoOriginal;
        }
    })
    habilitarBoton();
}

//Validar que la contraseña tenga al menos una minúscula
function validarUnaMinuscula() {
    let resultadoMinuscula=document.getElementById("minuscula");
    let textoOriginal=resultadoMinuscula.innerHTML;

    contraseña.addEventListener("input",()=>{
        let esValida=/[a-z]/.test(contraseña.value);

        if (esValida) {
            resultadoMinuscula.style.color="green";
            resultadoMinuscula.innerHTML=`<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"><path fill="currentColor" d="m10.6 16.6l7.05-7.05l-1.4-1.4l-5.65 5.65l-2.85-2.85l-1.4 1.4zM12 22q-2.075 0-3.9-.788t-3.175-2.137T2.788 15.9T2 12t.788-3.9t2.137-3.175T8.1 2.788T12 2t3.9.788t3.175 2.137T21.213 8.1T22 12t-.788 3.9t-2.137 3.175t-3.175 2.138T12 22m0-2q3.35 0 5.675-2.325T20 12t-2.325-5.675T12 4T6.325 6.325T4 12t2.325 5.675T12 20m0-8"/></svg>
            Al menos una minúscula.`;
        }else{
            resultadoMinuscula.style.color="";
            resultadoMinuscula.innerHTML=textoOriginal;
        }
    })
    habilitarBoton();
}

//Validar que la contraseña tenga al menos un número
function validarUnNumero() {
    let resultadoNumero=document.getElementById("numero");
    let textoOriginal=resultadoNumero.innerHTML;

    contraseña.addEventListener("input",()=>{
        let esValida=/[0-9]/.test(contraseña.value);

        if (esValida) {
            resultadoNumero.style.color="green";
            resultadoNumero.innerHTML=`<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"><path fill="currentColor" d="m10.6 16.6l7.05-7.05l-1.4-1.4l-5.65 5.65l-2.85-2.85l-1.4 1.4zM12 22q-2.075 0-3.9-.788t-3.175-2.137T2.788 15.9T2 12t.788-3.9t2.137-3.175T8.1 2.788T12 2t3.9.788t3.175 2.137T21.213 8.1T22 12t-.788 3.9t-2.137 3.175t-3.175 2.138T12 22m0-2q3.35 0 5.675-2.325T20 12t-2.325-5.675T12 4T6.325 6.325T4 12t2.325 5.675T12 20m0-8"/></svg>
            Al menos un número.`;
        }else{
            resultadoNumero.style.color="";
            resultadoNumero.innerHTML=textoOriginal;
        }
    })
    habilitarBoton();
}

//Validar que la contraseña tenga al menos un carácter especial
function validarUnCaracterEspecial() {
    let resultadoCaracterEspecial=document.getElementById("caracterEspecial");
    let textoOriginal=resultadoCaracterEspecial.innerHTML;

    contraseña.addEventListener("input",()=>{
    let esValida = /[._,:;+<>#@¿?!¡=~|º{}\[\]()¨\/-]/.test(contraseña.value);

        if (esValida) {
            resultadoCaracterEspecial.style.color="green";
            resultadoCaracterEspecial.innerHTML=`<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"><path fill="currentColor" d="m10.6 16.6l7.05-7.05l-1.4-1.4l-5.65 5.65l-2.85-2.85l-1.4 1.4zM12 22q-2.075 0-3.9-.788t-3.175-2.137T2.788 15.9T2 12t.788-3.9t2.137-3.175T8.1 2.788T12 2t3.9.788t3.175 2.137T21.213 8.1T22 12t-.788 3.9t-2.137 3.175t-3.175 2.138T12 22m0-2q3.35 0 5.675-2.325T20 12t-2.325-5.675T12 4T6.325 6.325T4 12t2.325 5.675T12 20m0-8"/></svg>
            Al menos un carácter especial.`;
        }else{
            resultadoCaracterEspecial.style.color="";
            resultadoCaracterEspecial.innerHTML=textoOriginal;
        }
    })
    habilitarBoton();
}

//Validar que el input de contraseña y de repetir contraseña coinciden
function validarInputsCoinciden() {
    let resultadoRepetirContraseña=document.getElementById("contraseñasRepetidas");
    let textoOriginal=resultadoRepetirContraseña.innerHTML;

    if (contraseña.value==repetirContraseña.value && contraseña.value!=="") {
        resultadoRepetirContraseña.style.color="green";
        resultadoRepetirContraseña.innerHTML= `<svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24"><path fill="currentColor" d="m10.6 16.6l7.05-7.05l-1.4-1.4l-5.65 5.65l-2.85-2.85l-1.4 1.4zM12 22q-2.075 0-3.9-.788t-3.175-2.137T2.788 15.9T2 12t.788-3.9t2.137-3.175T8.1 2.788T12 2t3.9.788t3.175 2.137T21.213 8.1T22 12t-.788 3.9t-2.137 3.175t-3.175 2.138T12 22m0-2q3.35 0 5.675-2.325T20 12t-2.325-5.675T12 4T6.325 6.325T4 12t2.325 5.675T12 20m0-8"/>
            </svg> Se repiten las contraseñas.`;
    } else {
        resultadoRepetirContraseña.style.color="";
        resultadoRepetirContraseña.innerHTML=textoOriginal;
    }
    habilitarBoton();
}

//El botón se habilita cuando se cumplen todas las condiciones
let botonRegistrarse = document.getElementById("registrarse");
function habilitarBoton() {
    let longitudValida=contraseña.value.length>=8;
    let mayusculaValida=/[A-Z]/.test(contraseña.value);
    let minusculaValida=/[a-z]/.test(contraseña.value);
    let numeroValido=/[0-9]/.test(contraseña.value);
    let caracterEspecialValido=/[._,:;+<>#@¿?!¡=~|º{}\[\]()¨\/-]/.test(contraseña.value);
    let coinciden=contraseña.value!=""&& contraseña.value==repetirContraseña.value;

    botonRegistrarse.disabled=!(longitudValida && mayusculaValida && minusculaValida && numeroValido && caracterEspecialValido && coinciden);
}

function configurarOjo(idInput, idBoton, idIcono) {
    const inputPass = document.getElementById(idInput);
    const btnPass = document.getElementById(idBoton);
    const icono = document.getElementById(idIcono);

    btnPass.addEventListener('click', () => {
        // Cambiar tipo de input
        if (inputPass.type === "password") {
            inputPass.type = "text";
            
            // Cambiar a icono de OJO ABIERTO
            icono.innerHTML = `
                <path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z"/>
                <path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8zm8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z"/>
            `;
            icono.classList.remove("bi-eye-slash-fill");
            icono.classList.add("bi-eye-fill");
        } else {
            inputPass.type = "password";

            // Cambiar a icono de OJO CERRADO (tachado)
            icono.innerHTML = `
                <path d="m10.79 12.912-1.614-1.615a3.5 3.5 0 0 1-4.474-4.474l-2.06-2.06C.938 6.278 0 8 0 8s3 5.5 8 5.5a7.029 7.029 0 0 0 2.79-.588zM5.21 3.088A7.028 7.028 0 0 1 8 2.5c5 0 8 5.5 8 5.5s-.939 1.721-2.641 3.238l-2.062-2.062a3.5 3.5 0 0 0-4.474-4.474L5.21 3.089z"/>
                <path d="M5.525 7.646a2.5 2.5 0 0 0 2.829 2.829l-2.83-2.829zm4.95.708-2.829-2.83a2.5 2.5 0 0 1 2.829 2.829zm3.171 6-12-12 .708-.708 12 12-.708.708z"/>
            `;
            icono.classList.remove("bi-eye-fill");
            icono.classList.add("bi-eye-slash-fill");
        }
    });
}

// Inicializar para ambos campos
document.addEventListener("DOMContentLoaded", () => {
    configurarOjo('contraseña', 'btnVerPass', 'iconoPass');
    configurarOjo('repetirContraseña', 'btnVerPass2', 'iconoPass2');
});

/*LLAMADA A FUNCIONES*/
validarLongitudContraseña();
validarUnaMayuscula();
validarUnaMinuscula();
validarUnNumero();
validarUnCaracterEspecial();

contraseña.addEventListener("input", validarInputsCoinciden);
repetirContraseña.addEventListener("input", validarInputsCoinciden);