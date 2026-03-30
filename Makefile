build:
	docker build -f DockerfileBuild -t bhcosta90/bhcosta90:base .
	docker push bhcosta90/bhcosta90:base