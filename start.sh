#!/bin/bash

echo "Iniciando contenedores del microservicio..."
docker compose up -d --build

if [ $? -eq 0 ]; then
  echo "Contenedores iniciados correctamente."
  echo "Acceso a la API en: http://localhost:8000"
else
  echo "Error al levantar los contenedores."
  exit 1
fi
