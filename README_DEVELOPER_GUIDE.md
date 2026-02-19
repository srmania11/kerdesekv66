# **Kérdezz-Felelek Rendszer (Q\&A) \- Fejlesztői Dokumentáció**

Ez a dokumentum a weboldalba integrált Kérdezz-Felelek (Q\&A) modul működését, fájlstruktúráját és adatbázis-logikáját írja le.

## **1\. Integrációs Alapelvek (NAGYON FONTOS\!)**

A rendszer **NEM** kezel saját regisztrációt és bejelentkezést. Egy meglévő (pl. WordPress) rendszerre épül rá.

### **Felhasználókezelés Logikája**

A rendszer a környezetből (a meglévő PHP oldalból) kapja meg a felhasználói adatokat két globális változó formájában:

* **$usr\_id**: A felhasználó egyedi azonosítója (ID) az eredeti (pl. WordPress) adatbázisból.  
* **$usr\_username**: A felhasználó neve.

**Működési logika:**

1. A PHP kód minden oldal betöltésekor ellenőrzi a $usr\_id változót.  
2. **Ha $usr\_id létezik és nagyobb mint 0:** A felhasználó **BE VAN JELENTKEZVE**.  
   * Ekkor a rendszer betölti a szerkesztőfelületeket, a válasz írása gombot, és a qc\_users táblából lekéri a fórum-specifikus adatokat (pontszám, rang).  
   * Ha a qc\_users táblában még nincs ilyen ID, a rendszer automatikusan létrehoz egy alapbejegyzést neki (jelszó nélkül, csak statisztikákra).  
3. **Ha $usr\_id üres vagy 0:** A látogató **VENDÉG**.  
   * Ekkor a "Vendég nézet" fájlok töltődnek be.  
   * A szerkesztők helyett figyelmeztető üzenetek jelennek meg ("Jelentkezz be\!").

## **2\. Fájlstruktúra és Útválasztás**

A rendszer az alábbi PHP fájlokból épül fel. Minden fájl egy konkrét célt szolgál.

### **Főoldal és Navigáció**

* **index.php (FŐOLDAL):**  
  * Ez a rendszer belépési pontja.  
  * Itt jelennek meg a Főkategóriák (pl. Hardver) és az Alkategóriák (pl. Videókártyák).  
  * Statisztikákat mutat (hány kérdés van, utolsó aktivitás).  
  * Innen navigál a felhasználó a kategoria.php-ra.  
* **kategoria.php (Kategória Böngészés):**  
  * Akkor töltődik be, ha a felhasználó rákattint egy alkategóriára (pl. "Videókártyák").  
  * Listázza a kérdéseket (qc\_questions tábla).  
  * Szűrési lehetőségeket és lapozást tartalmaz.

### **Kérdések Kezelése**

* **kerdes\_bevitele.php (Új Kérdés):**  
  * Csak bejelentkezett felhasználóknak ($usr\_id \> 0\) érhető el.  
  * Tartalmazza az űrlapot: Cím, Kategória választó, Rich Text Editor (képpel, videóval).  
  * Mentéskor új sort hoz létre a qc\_questions táblában.  
* **kerdes\_bejelentkezett\_nezet.php (Kérdés megtekintése \- User):**  
  * Akkor töltődik be, ha $usr\_id \> 0\.  
  * Mutatja a kérdést, a válaszokat.  
  * **EXTRA:** Megjeleníti a válaszíró szerkesztőt, a szavazó gombokat (+/-), és a "Megoldásnak jelölés" gombot.  
* **kerdes\_nem\_bejelentkezett\_nezet.php (Kérdés megtekintése \- Vendég):**  
  * Akkor töltődik be, ha $usr\_id \== 0 vagy nincs beállítva.  
  * Mutatja a kérdést és a válaszokat (olvasási mód).  
  * **EXTRA:** A szerkesztő helyett egy "Jelentkezz be a válaszadáshoz" figyelmeztető dobozt jelenít meg.

### **Kommunikáció és Moderáció**

* **private\_messages.php (Üzenetek Főoldal):**  
  * A bejövő és kimenő üzenetek listája (Inbox).  
  * Kétoszlopos elrendezés: Balra a partnerek listája, jobbra a chat ablak.  
  * Érzékeli az olvasott/olvasatlan státuszt.  
* **send\_private\_message.php (Új Üzenet):**  
  * Külön felület, ha valaki nem a chatből, hanem "nulláról" akar új embernek írni.  
  * Címzett kereső mezővel és szerkesztővel.  
* **tartalom\_jelentese.php (Jelentés):**  
  * Bármilyen tartalomnál (kérdés, válasz, üzenet) elérhető a "Jelentés" gomb.  
  * Ez a fájl tartalmazza az űrlapot, ahol a felhasználó kiválasztja a szabálysértés okát (pl. Spam, Sértő).

## **3\. Adatbázis Magyarázat (SQL logika)**

Az adatbázis táblái qc\_ előtaggal vannak ellátva, hogy ne keveredjenek a WordPress tábláival.

### **qc\_users (Kiegészítő felhasználói adatok)**

Nem tárolunk jelszót\! Csak a fórumhoz szükséges extrákat.

* user\_id: **KULCS\!** Ez köti össze a WordPress-szel.  
* reputation\_points: A felhasználó hasznosságát mérő pontszám.  
* custom\_title: Pl. "Hardver Szakértő" titulus.

### **qc\_categories (Kategória fa)**

* parent\_id: Ha ez NULL, akkor Főkategória (pl. Hardver). Ha van benne szám, akkor Alkategória (pl. Videókártya), ami a Főkategóriához tartozik.  
* slug: Az URL-ben megjelenő szép név (pl. hardver-videokartyak).

### **qc\_questions (A Kérdések)**

Ez a legfontosabb tábla.

* user\_id: Aki kérdezett. Ha NULL, akkor vendég kérdezte.  
* is\_guest & guest\_name: Ha a user\_id NULL, akkor itt tároljuk, hogy "Kovács Pisti" kérdezett vendégként.  
* status:  
  * 'open': Nyitott, várja a válaszokat (alapértelmezett).  
  * 'solved': **MEGOLDVA.** Ezt akkor állítjuk be, ha a kérdező elfogadott egy választ (zöld pipa).  
  * 'closed': **LEZÁRT.** Ide már nem lehet írni (pl. moderátor lezárta, lakat ikon).  
* vote\_score: A kérdésre érkezett szavazatok összege.

### **qc\_answers (A Válaszok)**

A válaszok a kérdésekhez (question\_id) tartoznak.

* is\_accepted: **1 (IGEN) / 0 (NEM)**. Ez jelzi a "Zöld keretes" elfogadott megoldást. Egy kérdéshez csak egy ilyen tartozhat.  
* is\_ai: **1 (IGEN) / 0 (NEM)**. Ha ez 1, akkor a rendszer kék hátterű "Mesterséges Intelligencia" választ jelenít meg.  
* user\_id: Ha NULL, akkor AI vagy Vendég válaszolt (lásd is\_ai vagy is\_guest mezőket).

### **qc\_pm\_threads és qc\_pm\_messages (Privát Üzenetek)**

* qc\_pm\_threads: Ez egy "szoba" két felhasználó (user\_one és user\_two) között.  
* qc\_pm\_messages: Ezek a konkrét üzenetek a szobán belül.  
* is\_read: Ha 0, akkor az üzenet **OLVASATLAN** (félkövér, kék pötty). Ha a címzett megnyitja, átváltjuk 1-re.

### **qc\_reports (Jelentések)**

* target\_type: Mit jelentettek? ('question', 'answer', 'pm').  
* target\_id: A jelentett tartalom ID-ja.  
* reason\_category: Miért jelentették? (pl. 'spam').

## **4\. Összefoglaló a fejlesztőnek**

* **Beágyazás:** A fenti HTML/PHP fájlokat include vagy require segítségével húzd be a fő keretrendszeredbe (layoutba), a megfelelő tartalom div-be (\<div class="content"\>...\</div\>).  
* **CSS:** Minden generált kód tartalmazza a szükséges CSS-t \<style\> tagek között, de érdemes ezeket egy közös .css fájlba kiszervezni a véglegesítésnél.  
* **Adatbázis:** Futtasd le a db\_schema.sql fájlt a phpMyAdmin-ban. Nem fogja törölni a meglévő adataidat (IF NOT EXISTS van benne), csak létrehozza a qc\_ táblákat.