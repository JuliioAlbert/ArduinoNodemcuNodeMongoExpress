
#include <ArduinoJson.h>
#include <ESP8266WiFi.h>
#include <ESP8266HTTPClient.h>

// Pines para los LEDs
#define LEDVERDE D2
#define LEDAMARILLO D3
#define LEDROJO D4
#define ANALOGPILA A0

// Variables
int analogValor = 0;
float voltaje = 0;
int ledDelay = 800;

// Umbrales
float maximo = 1.3;
//float medio = 1.4;
float medio = 0.78;
float minimo = 0.3;

//String Server
String server = "http://kumbia.herokuapp.com/pila/5eabaf5704e806002431cb4d";

const int BOTON=D7;
int val;

void setup()
{
  Serial.begin(115200);
  
  //pinMode(BOTON,INPUT);

   // Los pines de LED en modo salida
  pinMode(LEDVERDE, OUTPUT);
  pinMode(LEDAMARILLO, OUTPUT);
  pinMode(LEDROJO, OUTPUT);
  Serial.println();

  WiFi.begin("Julio-Wifi", "calcetin70");

  Serial.print("Connecting");
  while (WiFi.status() != WL_CONNECTED)
  {
    delay(500);
    Serial.print(".");
  }
  Serial.println();

  Serial.print("Connected, IP address: ");
  Serial.println(WiFi.localIP());
}

void loop() {
   // Leemos valor de la entrada analógica
  analogValor = analogRead(ANALOGPILA);

  // Obtenemos el voltaje
  voltaje = 0.0048 * analogValor;
  Serial.print("Voltaje: ");
  Serial.println(voltaje);

  // Dependiendo del voltaje mostramos un LED u otro
  if (voltaje >= maximo)
  {
    digitalWrite(LEDVERDE, HIGH);
    delay(ledDelay);
    digitalWrite(LEDVERDE, LOW);
  }
  else if (voltaje < maximo && voltaje > medio)
  {
    digitalWrite(LEDAMARILLO, HIGH);
    delay(ledDelay);
    digitalWrite(LEDAMARILLO, LOW);
  }
  else if (voltaje < medio && voltaje > minimo)
  {
    digitalWrite(LEDROJO, HIGH);
    delay(ledDelay);
    digitalWrite(LEDROJO, LOW);
  }

  // Apagamos todos los LEDs
  digitalWrite(LEDVERDE, LOW);
  digitalWrite(LEDAMARILLO, LOW);
  digitalWrite(LEDROJO, LOW);
   
  delay(1000);
  post(voltaje);
 
  }





  void post(float voltaje) {
 
   HTTPClient http;

  String json;
  StaticJsonBuffer<200> jsonBuffer;
  JsonObject& root = jsonBuffer.createObject();
  root["carga"] = voltaje;

  root.printTo(json);

  Serial.println(""); // salto de linea para http.writeToStream(&Serial);

  http.begin(server);
  http.addHeader("Content-Type", "application/json");
  http.POST(json);
  http.writeToStream(&Serial);
  http.end();
  
}
