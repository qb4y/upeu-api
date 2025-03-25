# API Storage

API de subida, visualización y eliminación de archivos en Minio, controlador `StorageController` contiene metodos
independientes`saveFile`,`deleteFile`,`checkFileExists`,`getUrlFile`,`responseFile` si en caso se necesita administrar el sistema de almacenamiento en otro proyecto
, copiar todo el controlador mencionado y configurar `config/filesystems.php`.


## 1. Subir archivo

`1.1. Url`

* POST [/api/storage]()
* POST [/api/storage/financial]()



`1.2. Parametros multipart/form-data`

|Parametro|Tipo|Validaciones|Ayuda|
| -------------| ------------- |-------------| -----|
|file|File| `requerido`, `multiple o solo un archivo`, `tipos: jpg,png,jpeg,pdf,xlsx,xls,xlsm,docx`| Puede enviar multiples archivos o un archivo |
|directory|String|`requerido`|directorios o subdirectorios ejemplo lamb-strategy-shell/orders|
|filename_persists|Boolean|`opcional`|si es verdadero, se conservara el nombre del archivo enviado, de lo contrario el servidor generará automaticamente (por defecto false)|

`1.3. Ejemplo de subida de multiples archivos, conservando el nombre original del archivo.`

```shell
curl --request POST \
  --url 'http://0.0.0.0:7501/api/storage?=' \
  --header 'Authorization: b2qje5zhbCuUdpS18TQnVLgyiIfhXZ' \
  --header 'Content-Type: multipart/form-data; boundary=---011000010111000001101001' \
  --cookie PHPSESSID=zLJRJV9W8gTA9w4PF8WUFksEuWz26W \
  --form 'file[0]=@/home/edwin/Downloads/GitHub-logo-1.jpg' \
  --form 'file[1]=@/home/edwin/Pictures/logo-upeu-color.png' \
  --form directory=test2 \
  --form keep_file_name=true
```

`1.4. Respuesta de subida de multiples archivos`

```json
{
    "success": true,
    "message": "file upload successfully",
    "data": [
        "test2/GitHub-logo-1.jpg",
        "test2/logo-upeu-color.png"
    ]
}
```

`1.5. Ejemplo de subida de un archivos.`

```shell
curl --request POST \
  --url 'http://0.0.0.0:7501/api/storage?=' \
  --header 'Authorization: b2qje5zhbCuUdpS18TQnVLgyiIfhXZ' \
  --header 'Content-Type: multipart/form-data; boundary=---011000010111000001101001' \
  --cookie PHPSESSID=zLJRJV9W8gTA9w4PF8WUFksEuWz26W \
  --form file=@/home/edwin/Downloads/GitHub-logo-1.jpg \
  --form directory=test2
```

`1.6. Respuesta de subida de un archivo`

```json
{
    "success": true,
    "message": "file upload successfully",
    "data": "test2/nma89TMI9w929muaphUorElr1BIv8zFVbFlXvLlR.jpg"
}
```

## 2. Obtener el archivo o la URL

`2.1. Url`

* GET [/api/storage]()
* GET [/api/storage/financial]()
`2.2. Parametros`

|Parametro|Tipo|Validaciones|Ayuda|
| -------------| ------------- |-------------| -----|
fileName|string|`requerido`| Nombre del archivo, guardado en la base de datos
download|boolean|`opcional`|Si se envia true retorna una visualizacion del archivo (por defecto false)


## 3. Eliminar el archivo

`3.1. Url`

* DELETE [/api/storage]()
* DELETE [/api/storage/financial]()

`3.2. Parametros`

|Parametro|Tipo|Validaciones|Ayuda|
| -------------| ------------- |-------------| -----|
fileName|string|`requerido`| Nombre del archivo, guardado en la base de datos


# Tomar en cuenta

* Cuando se sube el archivo se guarda en la ruta o directorio que definio al momento de guardar el archivo.
* Al guardar multiples archivos se envia un array, en ese mismo orden retorna los nombres de los archivos con los que
  posteriormente se deben buscar.
* Cuando se envia keep_file_name=true, debe ser único el nombre del archivo que se envia.
* Por defecto este proyecto guarda los archivos en el bucket `lamb-strategy`
* Al guardar el archivo(s) enviamos un parametro directory esto es clave para la ubicacion de guadado del archivo.
* El nombre del directorio es importante, los nombres que pueden usarse son los siguente:

######Proyectos que pueden usarse como nombre de `directorio`
* lamb-strategy-planning
* lamb-strategy-configuration
* lamb-strategy-management
* lamb-strategy-reporting
* lamb-strategy-shell
* lamb-strategy-documentary

######Ejemplo de directorio

* lamb-strategy-planning/clients/upeu
* lamb-strategy-planning/clients/upn

`lamb-strategy-planning` es el nombre del ***proyecto*** y seguidamente el lugar donde se guardará


# Configuración Minio

Agregar el siguente contenido en `config/filesystems.php` como un nuevo disco de almacenamiento, seguidamente configurar
en las variables de entorno `.env` y `.env.prod`.

```shell
'minio-lamb' => [
    'driver' => 's3',
    'endpoint' => env('LAMB_MINIO_ENDPOINT'),
    'use_path_style_endpoint' => true,
    'key' => env('LAMB_MINIO_KEY'),
    'secret' => env('LAMB_MINIO_SECRET'),
    'region' => env('LAMB_MINIO_REGION'),
    'bucket' => env('LAMB_MINIO_BUCKET'),
],
```

# TODO

* Buscar por solo nombre de archivo en todos los directorios del `bucket` y obtener la url.
