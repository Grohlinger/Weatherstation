

//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//Gas Detector MQ2                                                             +
//                                           Feuchtigkeits-Sensor KY-015       +
//                     Luftdruck-Sensor BMP280                         X  GND  +
//                                                                     X  +5V  +
//                                                                             +
//                        A3       A1                                  X  SDA  +
//                        X  X  X  X                                   X  SCL  +
//++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
#include <EEPROM.h>
#include <MQ2.h>
#include <DS3231.h>
#include <Wire.h>
#include <SPI.h>
#include <Adafruit_BMP280.h>
#include <DHT.h>
#include <WiFiNINA.h>

#define DHTPIN A1     
#define DHTTYPE DHT11
DHT dht(DHTPIN, DHTTYPE);
RTClib myRTC;

//begin original weather station
int smokesensor = A3;
float temp1 = 0;
float temp2 = 0;
float temperature = 0;
float pressure = 0;
float alt = 0;
float humidity = 0;
float lpg = 0;
float lastlpg = 0;
float co = 0;
float smoke = 0;

Adafruit_BMP280 bmp; 
MQ2 mq2(smokesensor);
//end original weather station

//begin dustsensor
int dustsensorpin = 8;
unsigned long duration;
unsigned long starttime;
unsigned long sampletime_ms = 30000;
unsigned long lowpulseoccupancy = 0;
float ratio = 0;
float dustconcentration = 0;
//end dustsensor
//begin Oxygensensor
const int OxygenSensor = A0;
float oxygenconcentration = 0;
//end Oxygensensor
//begin CO-Sensor
const int MQ9B_Pin = A2;
float MQ9B_Value = 0;
float MQ9B_Volt = 0; 
float ratio_co = 0; 
float co_concentration = 0;
const float euler = 2.71828183;
//end CO-Sensor

char ssid[] = "Livebox-0C2A";        // your network SSID (name)
char pass[] = "92A4E22598276296053E528336";    // your network password (use for WPA, or use as key for WEP)

int status = WL_IDLE_STATUS;
char server[] = "www.family-groh.eu";    

WiFiClient client;

String postData;

 
void setup() {
  Serial.begin(9600);
  while (!Serial) {
    ; // wait for serial port to connect. Needed for native USB port only
  }
  
  while (status != WL_CONNECTED) {
    Serial.print("Attempting to connect to SSID: ");
    Serial.println(ssid);
    status = WiFi.begin(ssid, pass);
    delay(10000);
  }
  Serial.println("Connected to WiFi");
  printWifiStatus();
  
  pinMode(dustsensorpin,INPUT);
  pinMode(OxygenSensor,INPUT);
  pinMode(MQ9B_Pin,INPUT);
  starttime = millis();//get the current time; 
  
  dht.begin();
  mq2.begin();

  Wire.begin();
  if (!bmp.begin()) {
    Serial.println(F("Could not find a valid BMP280 sensor, check wiring!"));
    while (1);
  }
Serial.println("year \tmonth \tday \thour \tmin \tsec \tunixtime [s]\tTemp. [°C]\tPress. [Pa]\tAlt. [m]\tHumid. [%]\tLPG [PPM] \tCO [PPM] \tSmoke [PPM] \tDust [pcs/l] \tO2 [%] \tCO [PPB] ");

}
 
void loop(){
    while (status != WL_CONNECTED) {
    Serial.print("Attempting to connect to SSID: ");
    Serial.println(ssid);
    status = WiFi.begin(ssid, pass);
    delay(10000);
    }
    duration = pulseIn(dustsensorpin, LOW);
    lowpulseoccupancy = lowpulseoccupancy+duration;
    
    if ((millis()-starttime) > sampletime_ms)
    {
    
    //begin read dustsensor
    ratio = lowpulseoccupancy/(sampletime_ms*10.0);  // Integer percentage 0=>100
    dustconcentration = 1.1*pow(ratio,3)-3.8*pow(ratio,2)+520*ratio+0.62; // using spec sheet curve
    //end read dustsensor
    
    temp1 = bmp.readTemperature();
    pressure = bmp.readPressure();
    alt = bmp.readAltitude(1019); // this should be adjusted to your local forcase
    humidity = dht.readHumidity();
    temp2 = dht.readTemperature();
    temperature = (temp1+temp2)/2;
     float* values= mq2.read(false); //set it false if you don't want to print the values in the Serial
     lpg = mq2.readLPG();
     if (lpg <0) {
      lpg = lastlpg;
     }
     lastlpg = lpg;
     co = mq2.readCO();
     smoke = mq2.readSmoke();

    oxygenconcentration = readConcentration();
    
    //begin read CO-Sensor
    MQ9B_Value = analogRead(MQ9B_Pin);
    MQ9B_Volt = MQ9B_Value/1023*5;
    ratio_co = ((5-MQ9B_Volt)/MQ9B_Volt)/0.75; // 0.75 is from calibrating the sensor at fresh air...
    //fit of the ratio Rs/R0 with the characteristic curve in the data sheet of the sensor yields the following equation for concentration:
    co_concentration = 1000000*pow(euler, (-2.01179739*ratio_co + 8.517193191));
    //end read CO-Sensort
    
    smoke = analogRead(smokesensor);
    DateTime now = myRTC.now();
 
    Serial.print(now.year(), DEC);
    Serial.print("\t");
    Serial.print(now.month(), DEC);
    Serial.print("\t");
    Serial.print(now.day(), DEC);
    Serial.print("\t");
    Serial.print(now.hour(), DEC);
    Serial.print("\t");
    Serial.print(now.minute(), DEC);
    Serial.print("\t");
    Serial.print(now.second(), DEC);
    Serial.print("\t");
    Serial.print(now.unixtime());
    Serial.print("\t");
    Serial.print(temperature);
    Serial.print("\t\t");
    Serial.print(pressure);
    Serial.print("\t");
    Serial.print(alt);
    Serial.print("\t\t");
    Serial.print(humidity);
    Serial.print("\t\t");
    Serial.print(lpg);
    Serial.print("\t\t");
    Serial.print(co);
    Serial.print("\t\t");
    Serial.print(smoke);
    Serial.print("\t\t");
    Serial.print(dustconcentration);
    Serial.print("\t\t");
    Serial.print(oxygenconcentration);
    Serial.print("\t");
    Serial.println(co_concentration);
    starttime = millis();
    lowpulseoccupancy = 0;


    //create post data string
    postData = "&temp="+String(temperature)+"&pressure="+String(pressure)+"&humidity="+String(humidity)+"&lpg="+String(lpg)+"&smoke="+String(smoke)+"&dustconcentration="+String(dustconcentration)+"&oxygenconcentration="+String(oxygenconcentration)+"&co_concentration="+String(co_concentration);
    
    //write data to server
    Serial.println("\nStarting connection to server...");
    // if you get a connection, report back via serial:
    if (client.connect(server, 80)) {
    Serial.println("connected to server");
    // Make a HTTP request:
    client.println("POST /weatherstation/post.php HTTP/1.1");
    client.println("Host: www.family-groh.eu");
    client.println("Content-Type: application/x-www-form-urlencoded");
    client.print("Content-Length: ");
    client.println(postData.length());
    client.println();
    client.print(postData);
    Serial.println(postData);
    Serial.println(postData.length());
  }
  
  while (client.available()) {
    char c = client.read();
    Serial.write(c);
  }

  // if the server's disconnected, stop the client:
  if (!client.connected()) {
    Serial.println();
    Serial.println("disconnecting from server.");
    client.stop();

    // do nothing forevermore:
    while (true);
    }
 
  }
}


//two Oxygen Sensor functions follow
float readO2Vout()
{
    long sum = 0;
    for(int i=0; i<32; i++)
    {
        sum += analogRead(OxygenSensor);
    }
 
    sum >>= 5;
 
    float MeasuredVout = sum * (5 / 1023.0);
    return MeasuredVout;
}
 
float readConcentration()
{
    float MeasuredVout = readO2Vout();
    float Concentration_Percentage = MeasuredVout*21.5;
    return Concentration_Percentage;
}

void printWifiStatus() {
  // print the SSID of the network you're attached to:
  Serial.print("SSID: ");
  Serial.println(WiFi.SSID());

  // print your board's IP address:
  IPAddress ip = WiFi.localIP();
  Serial.print("IP Address: ");
  Serial.println(ip);

  // print the received signal strength:
  long rssi = WiFi.RSSI();
  Serial.print("signal strength (RSSI):");
  Serial.print(rssi);
  Serial.println(" dBm");
}
  
