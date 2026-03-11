# SCP Kupony a Akce

WordPress plugin pro správu **kuponů a akcí** s možností jejich zobrazení pomocí shortcodů.  
Plugin umožňuje vytvářet nabídky s logem e‑shopu, popisem, kuponovým kódem a affiliate odkazem.

Kupony lze zobrazovat:
- na samostatné stránce
- na podstránce s výpisem všech kuponů
- **přímo uvnitř článků** pomocí shortcodu

## Demo

https://xqe.cz/kupony/

---

# Funkce

- vlastní **Custom Post Type**
- rozlišení mezi:
  - **Kupon** (obsahuje kód)
  - **Akce** (pouze odkaz na nabídku)
- možnost označit nabídku jako **aktivní / neaktivní**
- zobrazení **loga e‑shopu**
- tlačítko pro **kopírování kuponového kódu**
- tlačítko **Přejít na eshop**
- shortcode pro:
  - jednu nabídku
  - seznam nabídek
- kupony lze **vložit i do článků**

---

# Custom Post Type

Plugin registruje vlastní typ příspěvku:

```
scp_offer
```

V administraci WordPressu se zobrazí jako:

```
Kupony & Akce
```

---

# Shortcody

## Zobrazení jedné nabídky

```
[nabidka id="123"]
```

Zobrazí konkrétní kupon nebo akci podle ID příspěvku.

Kupon lze vložit:
- do stránky
- do článku
- do landing page

---

## Výpis všech nabídek

```
[nabidky]
```

Zobrazí všechny aktivní nabídky.

### Filtry

Pouze kupony:

```
[nabidky type="kupon"]
```

Pouze akce:

```
[nabidky type="akce"]
```

Omezení počtu:

```
[nabidky count="5"]
```

---

# Struktura nabídky

Každá nabídka může obsahovat:

- název nabídky
- popis
- logo e‑shopu
- kuponový kód
- affiliate / akční URL
- typ nabídky (kupon / akce)
- stav aktivní / neaktivní

---

# Instalace

1. stáhnout plugin
2. nahrát do:

```
/wp-content/plugins/
```

3. aktivovat v administraci WordPressu

---

# Známé problémy

## Neaktivní kupony ve vyhledávání

Při použití WordPress vyhledávání se mohou zobrazovat i **neaktivní kupony**.

Důvod:
WordPress search ignoruje meta field:

```
_scp_aktivni
```

### Možné řešení

Použít filtr `pre_get_posts` a upravit search query.

Tato oprava zatím není implementována.

---

# Roadmap

Plánované funkce:

- filtrování kuponů na stránce
- automatická expirace kuponů
- Gutenberg blok
- lepší CSS stylování
- statistika kliknutí na affiliate odkazy

---

# Autor

Adam Hornof  

Blog:  
https://xqe.cz
