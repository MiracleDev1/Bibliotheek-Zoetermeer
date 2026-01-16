const questions = [
    { q: "Kies een droomsituatie:", a: [{t:"Vliegen op een draak", g:"Fantasy"}, {t:"Een misdaad oplossen", g:"Thriller"}, {t:"Daten in Parijs", g:"Romantiek"}, {t:"Reizen door de tijd", g:"Sci-Fi"}]},
    { q: "Wat voor sfeer zoek je?", a: [{t:"Magisch", g:"Fantasy"}, {t:"Spannend", g:"Thriller"}, {t:"Ontroerend", g:"Romantiek"}, {t:"Technologisch", g:"Sci-Fi"}]},
    { q: "Kies een favoriet icoon:", a: [{t:"Kroon", g:"Fantasy"}, {t:"Vergrootglas", g:"Thriller"}, {t:"Hart", g:"Romantiek"}, {t:"Robot", g:"Sci-Fi"}]},
    { q: "Waar wil je nu zijn?", a: [{t:"In een bos met elfen", g:"Fantasy"}, {t:"In een geheim lab", g:"Sci-Fi"}, {t:"In een knusse bibliotheek", g:"Biografie"}, {t:"In een spookhuis", g:"Horror"}]}
];

// Vul aan tot 50
while(questions.length < 50) {
    let base = questions[questions.length % 4];
    questions.push({ q: base.q + " (" + (questions.length + 1) + ")", a: base.a });
}

let idx = 0;
let scores = {};

function load() {
    const q = questions[idx];
    document.getElementById('question').innerText = q.q;
    document.getElementById('counter').innerText = `Vraag ${idx + 1} van 50`;
    document.getElementById('progress-fill').style.width = `${(idx/50)*100}%`;
    
    const optDiv = document.getElementById('options');
    optDiv.innerHTML = '';
    q.a.forEach(opt => {
        const b = document.createElement('button');
        b.className = 'option-btn';
        b.innerText = opt.t;
        b.onclick = () => {
            document.querySelectorAll('.option-btn').forEach(btn => btn.classList.remove('selected'));
            b.classList.add('selected');
            scores[idx] = opt.g;
            document.getElementById('next-btn').disabled = false;
        };
        optDiv.appendChild(b);
    });
}

document.getElementById('next-btn').onclick = () => {
    idx++;
    if(idx < 50) {
        load();
        document.getElementById('next-btn').disabled = true;
    } else {
        showResult();
    }
};

function showResult() {
    const box = document.querySelector('.quiz-box');
    document.getElementById('question').style.display = 'none';
    document.getElementById('options').style.display = 'none';
    document.getElementById('next-btn').style.display = 'none';
    document.getElementById('counter').style.display = 'none';
    document.getElementById('result').style.display = 'block';

    const counts = {};
    Object.values(scores).forEach(g => counts[g] = (counts[g] || 0) + 1);
    const winner = Object.keys(counts).reduce((a, b) => counts[a] > counts[b] ? a : b);
    
    document.getElementById('genre-match').innerText = winner;
    document.getElementById('genre-desc').innerText = `Geweldig! Je bent een type voor ${winner} boeken.`;
}

load();