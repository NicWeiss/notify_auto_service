# собрать из докерфайла
cd notify_auto_service
sudo docker build -f ./docker/backend/Dockerfile -t nicharbor.com/notifier/backend:latest .
sudo docker build -f ./docker/frontend/Dockerfile --target prod   -t nicharbor.com/notifier/frontend:latest .

# сделатьо браз из контейнера
docker commit notifier_grafana_agent nicharbor.com/notifier/grafana_agent:latest

# тегнуть существующий, сторонний образ
docker tag mysql nicharbor.com/notifier/mysql:latest

# запушить образ в харбор
docker push nicharbor.com/notifier/frontend:latest
