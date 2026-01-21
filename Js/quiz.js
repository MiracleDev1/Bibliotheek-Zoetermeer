// ------------------------------------------------------------
// QUIZ: Genre keuze
// Werking:
// 1) We hebben een lijst met vragen + antwoordknoppen
// 2) Elk antwoord geeft +1 punt aan een genre
// 3) Na de laatste vraag kiezen we het genre met de meeste punten
// ------------------------------------------------------------


// ------------------------------------------------------------
// 1) VRAGEN + ANTWOORDEN
// q = de vraagtekst
// a = antwoorden (array)
// t = tekst op de knop
// g = genre dat een punt krijgt als je dat antwoord kiest
// ------------------------------------------------------------
const questions = [
  { q: "Wat voor sfeer zoek je?", a: [
    { t:"Magisch",   g:"Fantasy"   },
    { t:"Spannend",  g:"Thriller"  },
    { t:"Liefdevol", g:"Romantiek" },
    { t:"Technisch", g:"Sci-Fi"    }
  ]},

  { q: "Kies een omgeving:", a: [
    { t:"Een kasteel",     g:"Fantasy"  },
    { t:"Donker steegje",  g:"Horror"   },
    { t:"Parijs",          g:"Romantiek"},
    { t:"Een ruimteschip", g:"Sci-Fi"   }
  ]},

  { q: "Wat lees je graag over de hoofdpersoon?", a: [
    { t:"Hun levensverhaal", g:"Biografie" },
    { t:"Hun angsten",       g:"Horror"    },
    { t:"Hun missie",        g:"Thriller"  },
    { t:"Hun jeugd",         g:"Jeugd"     }
  ]},

  { q: "Welk tijdperk spreekt je aan?", a: [
    { t:"Middeleeuwen", g:"Historie" },
    { t:"Toekomst",     g:"Sci-Fi"   },
    { t:"Heden",        g:"Thriller" },
    { t:"Schooltijd",   g:"Jeugd"    }
  ]},

  { q: "Wat mag niet ontbreken?", a: [
    { t:"Draken",   g:"Fantasy"   },
    { t:"Moord",    g:"Thriller"  },
    { t:"Bruiloft", g:"Romantiek" },
    { t:"Robot",    g:"Sci-Fi"    }
  ]},

  { q: "Hoe moet het boek eindigen?", a: [
    { t:"Ze leefden lang en gelukkig", g:"Romantiek" },
    { t:"De dader is gepakt",          g:"Thriller"  },
    { t:"De wereld is gered",          g:"Fantasy"   },
    { t:"Een open einde",              g:"Sci-Fi"    }
  ]},

  { q: "Kies een voorwerp:", a: [
    { t:"Toverstaf",     g:"Fantasy"  },
    { t:"Vergrootglas",  g:"Thriller" },
    { t:"Dagboek",       g:"Biografie"},
    { t:"Oud zwaard",    g:"Historie" }
  ]},

  { q: "Wat voor gevoel wil je?", a: [
    { t:"Adrenaline",   g:"Thriller"  },
    { t:"Angst",        g:"Horror"    },
    { t:"Ontspanning",  g:"Romantiek" },
    { t:"Inspiratie",   g:"Biografie" }
  ]},

  { q: "Voor wie is het boek?", a: [
    { t:"Voor mijzelf",  g:"Thriller" },
    { t:"Voor een tiener", g:"Jeugd"  },
    { t:"Voor een dromer", g:"Fantasy"},
    { t:"Voor een denker", g:"Biografie"}
  ]},

  { q: "Wat vind je interessant?", a: [
    { t:"Echte feiten",     g:"Biografie" },
    { t:"Oude oorlogen",    g:"Historie"  },
    { t:"Nieuwe planeten",  g:"Sci-Fi"    },
    { t:"Geesten",          g:"Horror"    }
  ]},

  { q: "Welke kleur kies je?", a: [
    { t:"Goud",     g:"Fantasy"  },
    { t:"Bloedrood",g:"Horror"   },
    { t:"Roze",     g:"Romantiek"},
    { t:"Grijs",    g:"Historie" }
  ]},

  { q: "Wat doe je in je vrije tijd?", a: [
    { t:"Gamen",             g:"Sci-Fi"    },
    { t:"Sporten",           g:"Thriller"  },
    { t:"Lezen over sterren",g:"Biografie" },
    { t:"Buitenspelen",      g:"Jeugd"     }
  ]},

  { q: "Kies een icoon:", a: [
    { t:"🏰", g:"Fantasy"   },
    { t:"🚀", g:"Sci-Fi"    },
    { t:"❤️", g:"Romantiek" },
    { t:"🛡️", g:"Historie"  }
  ]},

  { q: "Wat is je favoriete vak?", a: [
    { t:"Geschiedenis", g:"Historie" },
    { t:"Natuurkunde",  g:"Sci-Fi"   },
    { t:"Nederlands",   g:"Biografie"},
    { t:"Gym",          g:"Jeugd"    }
  ]},

  { q: "Laatste vraag: kies een dier:", a: [
    { t:"Feniks", g:"Fantasy"  },
    { t:"Wolf",   g:"Horror"   },
    { t:"Hond",   g:"Jeugd"    },
    { t:"Uil",    g:"Biografie"}
  ]}
];


// ------------------------------------------------------------
// 2) VARIABELEN OM DE QUIZ BIJ TE HOUDEN
// currentIdx = welke vraag je nu zit (0 = eerste vraag)
// scores = punten per genre
// ------------------------------------------------------------
let currentIdx = 0;

let scores = {
  "Fantasy": 0,
  "Thriller": 0,
  "Romantiek": 0,
  "Sci-Fi": 0,
  "Historie": 0,
  "Horror": 0,
  "Biografie": 0,
  "Jeugd": 0
};


// ------------------------------------------------------------
// 3) HELPER: AANTAL VRAGEN
// Handig zodat je niet overal "15" hoeft te typen.
// ------------------------------------------------------------
const totalQuestions = questions.length;


// ------------------------------------------------------------
// 4) FUNCTIE: LAAD 1 VRAAG OP HET SCHERM
// - Zet de vraagtekst
// - Maakt knoppen voor de antwoorden
// - Update de progress bar
// ------------------------------------------------------------
function loadQuestion() {

  // De huidige vraag ophalen uit de lijst
  const currentQuestion = questions[currentIdx];

  // Vraagtekst in de HTML zetten
  document.getElementById("question-text").innerText = currentQuestion.q;

  // Container waar de antwoordknoppen in komen
  const optionsContainer = document.getElementById("options-container");

  // Eerst leegmaken (anders blijven oude knoppen staan)
  optionsContainer.innerHTML = "";

  // Progress bar updaten (hoeveel % gedaan?)
  const progressPercent = (currentIdx / totalQuestions) * 100;
  document.getElementById("progress-fill").style.width = progressPercent + "%";

  // Voor elk antwoord: maak een button
  currentQuestion.a.forEach(function(option) {

    // Button maken
    const button = document.createElement("button");
    button.className = "option-btn";
    button.innerText = option.t;

    // Als je klikt:
    // 1) +1 punt bij het genre
    // 2) ga naar de volgende vraag
    button.onclick = function() {
      scores[option.g] = scores[option.g] + 1;
      nextQuestion();
    };

    // Button toevoegen aan de container
    optionsContainer.appendChild(button);
  });
}


// ------------------------------------------------------------
// 5) FUNCTIE: VOLGENDE VRAAG
// - currentIdx + 1
// - Als er nog vragen zijn: loadQuestion()
// - Anders: showResult()
// ------------------------------------------------------------
function nextQuestion() {
  currentIdx = currentIdx + 1;

  if (currentIdx < totalQuestions) {
    loadQuestion();
  } else {
    showResult();
  }
}


// ------------------------------------------------------------
// 6) FUNCTIE: RESULTAAT TONEN
// - Kijkt welk genre de meeste punten heeft
// - Schrijft resultaat HTML in de quiz container
// ------------------------------------------------------------
function showResult() {

  // Winner bepalen (genre met hoogste score)
  let winner = Object.keys(scores).reduce(function(bestGenreSoFar, currentGenre) {

    // Als currentGenre meer punten heeft dan bestGenreSoFar -> vervang
    if (scores[currentGenre] > scores[bestGenreSoFar]) {
      return currentGenre;
    } else {
      return bestGenreSoFar;
    }

  });

  // Resultaat in HTML tonen
  document.getElementById("quiz-container").innerHTML = `
    <h2>Klaar!</h2>
    <p>Jouw ideale genre is:</p>
    <h1 style="color:var(--accent); margin:20px 0;">${winner}</h1>
    <a href="${winner.toLowerCase()}.html" class="btn">Bekijk ${winner} boeken</a>
  `;
}


// ------------------------------------------------------------
// 7) STARTEN
// Meteen de eerste vraag laden
// ------------------------------------------------------------
loadQuestion();
