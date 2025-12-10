function textoValidarContraseña() {
    document.getElementById("requisitosContraseña").style.display="block";
}
//VALIDACIÓN DE CONTRASEÑA
let contraseña=document.getElementById("contraseña");
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
}

//Validar que la contraseña tenga al menos un carácter especial
function validarUnCaracterEspecial() {
    
}

/*LLAMADA A FUNCIONES*/
validarLongitudContraseña();
validarUnaMayuscula();
validarUnaMinuscula();
validarUnNumero();