from locust import HttpUser, task, between

class WebAppUser(HttpUser):
    wait_time = between(1, 5)
    host = "https://pedago.univ-avignon.fr/~uapv2200995/application_gla_crypto"

    @task
    def homepage(self):
        self.client.get("/index.php") 
        
    @task
    def check_alerts(self):
        self.client.get("/checkAlerts.php")

