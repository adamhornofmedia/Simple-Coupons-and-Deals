# Simple Coupons & Deals

> [English](#english) | [Česky](#česky)

---

<a name="english"></a>
# English

WordPress plugin for managing **coupons and deals** with shortcode-based display.  
Allows creating offers with a shop logo, description, coupon code, and affiliate link.

Coupons can be displayed:
- on a standalone page
- on a subpage listing all coupons
- **directly inside posts** using a shortcode

## Demo

https://xqe.cz/kupony/

---

## Features

- custom **Custom Post Type**
- distinction between:
  - **Coupon** (contains a code)
  - **Deal** (link to offer only)
- ability to mark an offer as **active / inactive**
- display of **shop logo**
- **copy coupon code** button
- **Go to store** button
- shortcode for:
  - a single offer
  - a list of offers
- coupons can be **embedded inside posts**

---

## Custom Post Type

The plugin registers a custom post type:

```
scp_offer
```

Displayed in the WordPress admin as:

```
Kupony & Akce
```

---

## Shortcodes

### Display a single offer

```
[nabidka id="123"]
```

Displays a specific coupon or deal by post ID.

Can be embedded in:
- a page
- a post
- a landing page

---

### List all offers

```
[nabidky]
```

Displays all active offers.

#### Filters

Coupons only:

```
[nabidky type="kupon"]
```

Deals only:

```
[nabidky type="akce"]
```

Limit count:

```
[nabidky count="5"]
```

---

## Offer structure

Each offer can contain:

- offer title
- description
- shop logo
- coupon code
- affiliate / deal URL
- offer type (coupon / deal)
- active / inactive status

---

## Installation

1. download the plugin
2. upload to:

```
/wp-content/plugins/
```

3. activate in the WordPress admin

---

## Known issues

### Inactive coupons in search results

WordPress search may return **inactive coupons**.

Reason:
WordPress search ignores the meta field:

```
_scp_aktivni
```

#### Possible fix

Use the `pre_get_posts` filter to adjust the search query.

This fix is not yet implemented.

---

## Roadmap

Planned features:

- Optimization for the official WordPress repository
- Gutenberg block
- Improved CSS styling
- Click statistics for affiliate links
- More languages
- Rebrand

---

## Author

Adam Hornof  
https://hornof.dev

---
---

<a name="česky"></a>
# Česky

WordPress plugin pro správu **kuponů a akcí** s možností jejich zobrazení pomocí shortcodů.  
Plugin umožňuje vytvářet nabídky s logem e‑shopu, popisem, kuponovým kódem a affiliate odkazem.

Kupony lze zobrazovat:
- na samostatné stránce
- na podstránce s výpisem všech kuponů
- **přímo uvnitř článků** pomocí shortcodu

## Demo

https://xqe.cz/kupony/

---

## Funkce

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

## Custom Post Type

Plugin registruje vlastní typ příspěvku:

```
scp_offer
```

V administraci WordPressu se zobrazí jako:

```
Kupony & Akce
```

---

## Shortcody

### Zobrazení jedné nabídky

```
[nabidka id="123"]
```

Zobrazí konkrétní kupon nebo akci podle ID příspěvku.

Kupon lze vložit:
- do stránky
- do článku
- do landing page

---

### Výpis všech nabídek

```
[nabidky]
```

Zobrazí všechny aktivní nabídky.

#### Filtry

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

## Struktura nabídky

Každá nabídka může obsahovat:

- název nabídky
- popis
- logo e‑shopu
- kuponový kód
- affiliate / akční URL
- typ nabídky (kupon / akce)
- stav aktivní / neaktivní

---

## Instalace

1. stáhnout plugin
2. nahrát do:

```
/wp-content/plugins/
```

3. aktivovat v administraci WordPressu

---

## Známé problémy

### Neaktivní kupony ve vyhledávání

Při použití WordPress vyhledávání se mohou zobrazovat i **neaktivní kupony**.

Důvod:
WordPress search ignoruje meta field:

```
_scp_aktivni
```

#### Možné řešení

Použít filtr `pre_get_posts` a upravit search query.

Tato oprava zatím není implementována.

---

## Roadmap

Plánované funkce:

- Optimalizace pro oficiální repozitář
- Gutenberg blok
- Lepší CSS stylování
- Statistika kliknutí na affiliate odkazy

---

## Autor

Adam Hornof  
https://hornof.dev
