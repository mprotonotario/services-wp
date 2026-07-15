#!/bin/bash

echo "Deteniendo contenedores del microservicio..."
docker compose down

if [ $? -eq 0 ]; then
  echo "Contenedores detenidos y eliminados correctamente."
else
  echo "Error al detener los contenedores."
  exit 1
fi
