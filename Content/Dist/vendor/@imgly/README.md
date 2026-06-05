Instrucciones para instalar @imgly/background-removal localmente

Objetivo:
Colocar una copia del paquete `@imgly/background-removal@1.4.5` dentro de
`Content/Dist/vendor/@imgly/background-removal@1.4.5/` para que `bg-remover.js`
lo cargue localmente (evita problemas de CSP/ESM con CDNs).

Archivos esperados:
- Content/Dist/vendor/@imgly/background-removal@1.4.5/dist/index.mjs
- Content/Dist/vendor/@imgly/background-removal@1.4.5/dist/* (archivos WASM, ONNX, assets)

Pasos recomendados:
1. En el equipo de desarrollo, descarga la release o el paquete desde npm/unpkg:

   a) Usando npm (requiere Node.js):

      npm pack @imgly/background-removal@1.4.5
      tar -xzf @imgly-background-removal-1.4.5.tgz
      mv package/* Content/Dist/vendor/@imgly/background-removal@1.4.5/

   b) O descarga manualmente los ficheros necesarios desde el CDN y colócalos
      en la ruta `Content/Dist/vendor/@imgly/background-removal@1.4.5/dist/`.

2. Asegúrate de que `dist/index.mjs` existe y que los archivos de modelos/WASM
   están en `dist/` tal como los espera la librería.

3. En el servidor, despliega la carpeta `Content/Dist/vendor/@imgly/background-removal@1.4.5/`.

Notas:
- Si no colocas la copia local, `bg-remover.js` seguirá intentando cargar desde
  CDN (`unpkg` o `cdn.jsdelivr.net`) como fallback.
- Si deseas usar otro release, crea la carpeta con el nombre `background-removal@<vers>`
  y actualiza `IMGLY_VERSION` en `Content/Dist/js/bg-remover.js` si es necesario.
