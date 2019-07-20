*create a VPC*

europe-west1: 10.132.0.0/20 = 10.132.0.1 - 10.132.15.254

    gcloud compute networks create fm-vpc --subnet-mode=auto

    gcloud compute firewall-rules create fm-vpc-allow-icmp --direction=INGRESS --priority=65534 --network=fm-vpc --action=ALLOW --rules=icmp --source-ranges=0.0.0.0/0

    gcloud compute firewall-rules create fm-vpc-allow-internal --direction=INGRESS --priority=65534 --network=fm-vpc --action=ALLOW --rules=all --source-ranges=10.128.0.0/9

    gcloud compute firewall-rules create fm-vpc-allow-ssh --direction=INGRESS --priority=65534 --network=fm-vpc --action=ALLOW --rules=tcp:22 --source-ranges=0.0.0.0/0

*create GCP Kubernetes engine Cluster*

    gcloud container clusters create fm-cluster --num-nodes 1 --min-nodes 1 --disk-size 10 --machine-type n1-standard-1 --network=fm-vpc

*service account*

    kubectl apply -f k8s/serviceaccount.yaml

*storage class pd-ssd*

    kubectl apply -f k8s/pd-ssd-storage-class.yaml

*install helm*

    kubectl apply -f k8s/helm-rbac.yaml
    helm init --service-account tiller

*create Redis cluster*

    helm install --name fm-redis --values k8s/redis-helm-values.yaml stable/redis

*add Redis password to app secrets*

    kubectl get secret --namespace default fm-redis -o jsonpath="{.data.redis-password}" | base64 --decode
    vim k8s/app-secrets.yaml

*create GCP Cloud SQL*

    gcloud beta sql instances create fm-db-sql --zone=europe-west1-b --tier=db-n1-standard-1 --storage-type=SSD --storage-size=10Gi --storage-auto-increase --backup --backup-start-time=03:00 --no-assign-ip --network=projects/sc-fleet-manager/global/networks/fm-vpc

*test mysql connection*

    gcloud beta compute instances list
    gcloud beta compute ssh xxxx

*add MySQL URI to app secrets*

    gcloud beta sql instances ......

*config app*

    kubectl apply -f k8s/app-config.yaml
    kubectl apply -f k8s/app-secrets.yaml

*launch app*

    kubectl apply -f k8s/app.yaml

*launch Traefik ingress*

    helm install --name fm-ingress --values k8s/traefik-helm-values.yaml k8s/helm/traefik
