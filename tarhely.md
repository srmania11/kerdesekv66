# Tárhely és Pontrendszer Működése

A rendszer dinamikusan számolja ki a felhasználó számára elérhető tárhelyet az aktivitása alapján.

## Alapértelmezett Tárhely
Minden regisztrált felhasználó automatikusan **10 MB** tárhelyet kap.

## Szintek és Bővítések
A tárhely növekszik, ha a felhasználó elér bizonyos mérföldköveket a feltett kérdések (`K`) vagy a megírt válaszok (`V`) száma alapján. A rendszer mindig a legmagasabb elért szintet veszi figyelembe.

| Szint | Feltétel (VAGY) | Tárhely |
| :--- | :--- | :--- |
| **Alap** | Regisztráció | 10 MB |
| **1. Szint** | 5 Kérdés / 10 Válasz | 15 MB |
| **2. Szint** | 30 Kérdés / 60 Válasz | 20 MB |
| **3. Szint** | 100 Kérdés / 200 Válasz | 30 MB |
| **4. Szint** | 200 Kérdés / 400 Válasz | 100 MB |
| **5. Szint** | 400 Kérdés / 800 Válasz | 500 MB |
| **6. Szint** | 500 Kérdés / 1000 Válasz | 800 MB |
| **7. Szint** | 700 Kérdés / 2000 Válasz | 1 GB |
| **8. Szint** | 1000 Kérdés / 4000 Válasz | 2 GB (Max) |

## Bónusz Rendszer
A maximális 2 GB-on felül további bónusz tárhely szerezhető az elfogadott válaszok után.

*   **Jutalom:** +200 MB
*   **Feltétel:** Minden 100. elfogadott válasz után.
*   **Limit:** Nincs felső határ, a 2 GB limiten felül is hozzáadódik.

## Technikai Működés
*   **Számítás:** A rendszer minden profilmegtekintéskor újrakalkulálja a limitet a fenti táblázat és a bónuszok alapján.
*   **Nyomonkövetés:** A foglalt tárhely (`storage_used`) az adatbázisban kerül tárolásra (`qc_users` tábla).
*   **Frissülés:**
    *   Fájl feltöltésekor a méret hozzáadódik.
    *   Fájl törlésekor (profilkép csere, kérdés szerkesztésekor kép törlése, kérdés/válasz törlése) a méret levonódik.
    *   A méret mindig a szerveren ténylegesen elfoglalt (tömörített WebP) méretet jelenti.
