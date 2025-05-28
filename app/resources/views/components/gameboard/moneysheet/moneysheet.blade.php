<div x-data="{open: false}">
    <button x-on:click="open = !open">Money Sheet<br/> xx.xxx€</button>
    <div x-show="open" class="moneysheet">
        <div class="moneysheet__income">
            <h2>Einnahmen</h2>
        </div>
        <div class="moneysheet__expenses">
            <h2>Ausgaben</h2>
        </div>
        <div class="moneysheet__income-sum">
            xx.xxx€
        </div>
        <div class="moneysheet__expenses-sum">
             - xx.xxx€
        </div>
        <div class="moneysheet__sum">
            = xx.xxx€
        </div>
    </div>
</div>
