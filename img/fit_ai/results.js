//przykład do wizualizji
const data = {
    totalKcal: 1800,
    meals: [
        {
            name: "Śniadanie",
            title: "Owsianka z owocami",
            kcal: 450,
            ingredients: [
                "60 g płatków owsianych",
                "250 ml mleka",
                "1 banan",
                "10 g orzechów"
            ],
            steps: [
                "Podgrzej mleko (2 min)",
                "Dodaj płatki i gotuj 5–6 min",
                "Dodaj banana",
                "Posyp orzechami"
            ],
            upgrade: "Możesz dodać nasiona chia lub jogurt."
        },
        {
            name: "Obiad",
            title: "Makaron warzywny",
            kcal: 750,
            ingredients: [
                "80 g makaronu pełnoziarnistego",
                "1 cukinia",
                "200 g pomidorów",
                "1 łyżka oliwy"
            ],
            steps: [
                "Ugotuj makaron al dente",
                "Podsmaż cukinię na oliwie",
                "Dodaj pomidory i duś 3 min",
                "Połącz z makaronem"
            ],
            upgrade: "Jeśli chcesz, dodaj tofu lub parmezan."
        },
        {
            name: "Kolacja",
            title: "Sałatka białkowa",
            kcal: 600,
            ingredients: [
                "Mix sałat",
                "150 g ciecierzycy",
                "1/2 awokado",
                "1 łyżka oliwy"
            ],
            steps: [
                "Umyj i osusz sałatę",
                "Pokrój awokado",
                "Dodaj ciecierzycę",
                "Polej oliwą i wymieszaj"
            ],
            upgrade: "Możesz dodać fetę lub pestki dyni."
        }
    ],
    shoppingList: [
        "Jogurt naturalny",
        "Nasiona chia",
        "Parmezan lub tofu",
        "Pestki dyni"
    ]
};


document.getElementById("totalKcal").textContent =
    data.totalKcal + " kcal";


const mealsContainer = document.getElementById("mealsContainer");

data.meals.forEach(meal => {
    const mealDiv = document.createElement("div");
    mealDiv.className = "meal compact";

    mealDiv.innerHTML = `
        <div class="meal-header">
            <h3>${meal.name}: ${meal.title}</h3>
            <span class="kcal">${meal.kcal} kcal</span>
        </div>

        <div class="meal-body">
            <div class="ingredients">
                <h4>Składniki</h4>
                <ul>
                    ${meal.ingredients.map(i => `<li>${i}</li>`).join("")}
                </ul>
            </div>

            <div class="steps">
                <h4>Sposób przygotowania</h4>
                <ol>
                    ${meal.steps.map(s => `<li>${s}</li>`).join("")}
                </ol>
            </div>
        </div>

        <p class="upgrade">${meal.upgrade}</p>
    `;

    mealsContainer.appendChild(mealDiv);
});

const shoppingUl = document.getElementById("shoppingList");

data.shoppingList.forEach(item => {
    const li = document.createElement("li");
    li.textContent = item;
    shoppingUl.appendChild(li);
});
