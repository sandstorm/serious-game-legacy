<?php

declare(strict_types=1);

namespace Domain\Definitions\Lebensziel;

use Domain\Definitions\Card\ValueObject\LebenszielPhaseId;
use Domain\Definitions\Card\ValueObject\MoneyAmount;
use Domain\Definitions\Lebensziel\Dto\LebenszielDefinition;
use Domain\Definitions\Lebensziel\Dto\LebenszielPhaseDefinition;
use Domain\Definitions\Lebensziel\ValueObject\LebenszielId;
use RuntimeException;

class LebenszielFinder
{
    /**
     * @return LebenszielDefinition[]
     */
    public static function getAllLebensziele(): array
    {
        $lebensziel1 = new LebenszielDefinition(
            id: LebenszielId::create(1),
            name: 'Aufbau einer Selbstversorgerfarm in Kanada',
            description: 'Du suchst einen Lebensstil, der auf Nachhaltigkeit statt auf Konsum beruht. Am liebsten arbeitest du unter freiem Himmel, lernst von Pflanzen und Tieren und lebst in einem Umfeld, das deine Werte widerspiegelt. Fernab des deutschen Konsumdrucks siehst du in Kanada die Chance, eine eigene Farm aufzubauen und dich weitgehend selbst zu versorgen.',
            phaseDefinitions: [
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_1,
                    description: 'Um deinem nachhaltigen Traum näherzukommen, beginnst du eine internationale Ausbildung in ökologischer Landwirtschaft, während du die Formalitäten für eine dauerhafte Aufenthaltsgenehmigung in Kanada erledigst.',
                    investitionen: new MoneyAmount(50000),
                    bildungsKompetenzSlots: 1,
                    freizeitKompetenzSlots: 2,
                ),
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_2,
                    description: 'Nach dem Abschluss planst du, am Programm World Wide Opportunities on Organic Farms (WWOOF) teilzunehmen und um die Welt zu reisen. Dabei lernst du verschiedenste Selbstversorgerprojekte kennen und knüpfst Kontakte zu Gleichgesinnten. Gestärkt durch dieses Netzwerk entscheidest du dich schließlich dafür, in Kanada deine eigene Farm zu gründen.',
                    investitionen: new MoneyAmount(100000),
                    bildungsKompetenzSlots: 2,
                    freizeitKompetenzSlots: 4,
                ),
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_3,
                    description: 'Deine Farm läuft erfolgreich und du nimmst internationale Freiwillige auf, die deinen ressourcenschonenden Lebensstil kennenlernen möchten. Durch Führungen und Vorträge machst du dein Konzept einem größeren Publikum bekannt. Wird es dir gelingen, mehr Menschen für ein nachhaltiges, konsumreduziertes Leben zu begeistern?',
                    investitionen: new MoneyAmount(150000),
                    bildungsKompetenzSlots: 3,
                    freizeitKompetenzSlots: 6,
                ),
            ],
        );

        $lebensziel2 = new LebenszielDefinition(
            id: LebenszielId::create(2),
            name: 'Aufforstung der Sahara in Niger',
            description: 'Du interessierst dich für Umweltschutz und beginnst bereits in der Schule, dich in entsprechenden Projekten einzubringen. Dir ist klar, dass du auch beruflich in diesem Bereich arbeiten und dort etwas bewegen möchtest.',
            phaseDefinitions: [
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_1,
                    description: 'Du planst, nach der Schule ein Freiwilliges Soziales Jahr (FSJ) in einem Aufforstungsprojekt in Niger zu absolvieren. Dort möchtest du praktische Erfahrungen sammeln und herausfinden, ob dich die Arbeit langfristig begeistert. Das Projekt gefällt dir so gut, dass du anschließend weiter dort arbeitest und dir durch Online-Weiterbildungen und Workshops zusätzliches Fachwissen aneignest.',
                    investitionen: new MoneyAmount(50000),
                    bildungsKompetenzSlots: 1,
                    freizeitKompetenzSlots: 2,
                ),
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_2,
                    description: 'Du übernimmst die Leitung des Aufforstungsprojekts in Niger und weist Freiwillige und Angestellte in ihre Aufgaben ein. Zukünftig verantwortest du außerdem die Öffentlichkeitsarbeit und baust dein berufliches Netzwerk weiter aus.',
                    investitionen: new MoneyAmount(100000),
                    bildungsKompetenzSlots: 2,
                    freizeitKompetenzSlots: 4,
                ),
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_3,
                    description: 'Um das Projekt weiter auszubauen, werden zusätzliche finanzielle Mittel benötigt. Du bist weltweit unterwegs, um Förderer zu werben und auf den Schutz der Wälder aufmerksam zu machen. Kannst du genügend Unterstützung gewinnen, um weitere Flächen aufzuforsten?',
                    investitionen: new MoneyAmount(150000),
                    bildungsKompetenzSlots: 3,
                    freizeitKompetenzSlots: 6,
                ),
            ],
        );

        $lebensziel3 = new LebenszielDefinition(
            id: LebenszielId::create(3),
            name: 'Aufbau einer Plattform für Reisecontent auf Social Media',
            description: 'Du willst nicht nur eigene Abenteuer teilen, sondern einen Ort schaffen, an dem Reisende aus aller Welt kurze, packende Eindrücke posten können – von Streetfood-Entdeckungen bis zu spontanen Citytrips. Deine Plattform soll Menschen verbinden, die mit wenig Planung viel erleben wollen, und ihnen ständig neue Impulse für das nächste Abenteuer geben.',
            phaseDefinitions: [
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_1,
                    description: 'Begleitend zu einem Studium in Medieninformatik entwirfst du einen Prototyp für deine Plattform. Dieser umfasst Kurzvideo-Uploads, eine Kartenansicht mit Hotspots und eine Chatfunktion. Eine kleine Testgruppe liefert Feedback, das du direkt einarbeitest.',
                    investitionen: new MoneyAmount(50000),
                    bildungsKompetenzSlots: 1,
                    freizeitKompetenzSlots: 2,
                ),
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_2,
                    description: 'Die Plattform geht online. Du planst, Promotionsaktionen in Hostels und auf Reisemessen zu organisieren, eigene Video-Features zu produzieren und Mitarbeitende für Entwicklung und Community-Support einzustellen. Dadurch kannst du erste Kooperationen mit Reisemarken aufbauen. Zusätzlich belegst du Kurse zu Vertrags- und Steuerfragen, um auch die unternehmerischen Aufgaben professionell zu bewältigen.',
                    investitionen: new MoneyAmount(100000),
                    bildungsKompetenzSlots: 3,
                    freizeitKompetenzSlots: 3,
                ),
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_3,
                    description: 'Deine Plattform wird von mehreren Millionen Menschen aktiv genutzt. Ein interdisziplinäres Team betreut Technik, Content-Moderation und Partner-Management. Du priorisierst zusätzliche Funktionen wie Live-Routen oder Gruppen-Challenges. Gelingt es dir, kontinuierlich neue Ideen umzusetzen und die Plattform zugleich technisch stabil weiterzuentwickeln?',
                    investitionen: new MoneyAmount(150000),
                    bildungsKompetenzSlots: 4,
                    freizeitKompetenzSlots: 5,
                ),
            ],
        );

        $lebensziel4 = new LebenszielDefinition(
            id: LebenszielId::create(4),
            name: 'Aufbau einer renommierten Anwaltskanzlei',
            description: 'Gerechtigkeit, Verlässlichkeit und die Bewahrung bewährter Strukturen prägen dein Denken. Du fühlst dich in der Welt der Paragrafen wohl, verfolgst politische Entscheidungsprozesse aufmerksam und verhandelst gerne belastbare Kompromisse. Dein Ziel ist eine Kanzlei, die für fachliche Exzellenz, klare Prinzipien und höchste Servicequalität steht. Eine zuverlässige Anlaufstelle für die Mandantschaft und Mitarbeitende gleichermaßen.',
            phaseDefinitions: [
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_1,
                    description: 'Du beginnst das Jurastudium, vertiefst dich in Staats-, Zivil- und Wirtschaftsrecht und gewinnst durch Praktika bei Gerichten, Ministerien und Kanzleien erste praktische Einblicke. Dabei klärst du für dich, welche Rechtsgebiete deine zukünftige Kanzlei abdecken soll.',
                    investitionen: new MoneyAmount(50000),
                    bildungsKompetenzSlots: 2,
                    freizeitKompetenzSlots: 1,
                ),
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_2,
                    description: 'Nun stehen das Schwerpunktstudium, das Erste Staatsexamen und das Referendariat an, durch die du deine fachlichen Kenntnisse weiter vertiefst. Parallel planst du, dein Netzwerk in Anwaltskammern und Fachverbänden auszubauen, Rhetorik- sowie Verhandlungsseminare zu besuchen und Gesetzesreformen zu verfolgen, um juristisch auf dem neuesten Stand zu bleiben.',
                    investitionen: new MoneyAmount(100000),
                    bildungsKompetenzSlots: 4,
                    freizeitKompetenzSlots: 2,
                ),
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_3,
                    description: 'Nach dem Zweiten Staatsexamen gewinnst du erste eigene Mandate, sammelst Berufserfahrung und führst komplexe Fälle. Mit einem stabilen Netzwerk und nachweisbaren Erfolgen gründest du deine Kanzlei, stellst qualifiziertes Personal ein und etablierst klare Qualitätsstandards. Gelingt es dir, die Zahl deiner Mandate und deine Reputation kontinuierlich zu steigern und die Kanzlei zu einer festen Größe am Markt zu entwickeln?',
                    investitionen: new MoneyAmount(150000),
                    bildungsKompetenzSlots: 6,
                    freizeitKompetenzSlots: 3,
                ),
            ],
        );

        $lebensziel5 = new LebenszielDefinition(
            id: LebenszielId::create(5),
            name: 'Aufbau einer Stiftung zur Förderung der Demokratie',
            description: 'Du engagierst dich leidenschaftlich für demokratische Werte und gesellschaftlichen Zusammenhalt. Anstatt nur einzelne Projekte zu unterstützen, willst du eine dauerhafte Institution schaffen: eine Stiftung, die Bildungsangebote, Forschung und zivilgesellschaftliche Initiativen zur Stärkung gesellschaftlicher Teilhabe fördert. Dein Ziel ist es, langfristig Strukturen zu verankern, die faire Mitsprache und Chancengleichheit sichern.',
            phaseDefinitions: [
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_1,
                    description: 'Du beginnst ein Studium in Politikwissenschaft und Verwaltung und absolvierst Praktika bei Nichtregierungsorganisationen (NGOs) sowie Stiftungen. Parallel startest du einen Podcast, in dem du aktuelle Demokratiethemen analysierst und erste Kontakte in Wissenschaft, Medien und Zivilgesellschaft knüpfst.',
                    investitionen: new MoneyAmount(50000),
                    bildungsKompetenzSlots: 2,
                    freizeitKompetenzSlots: 1,
                ),
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_2,
                    description: 'Nach dem Abschluss arbeitest du in einer gemeinnützigen Organisation und leitest kleinere Projekte, in denen du Fundraisingmethoden kennenlernst. Du möchtest nun die Satzung deiner künftigen Stiftung entwerfen, rechtliche Rahmenbedingungen klären, einen Beirat zusammenstellen und Startkapital bei Förderinstituten einwerben.',
                    investitionen: new MoneyAmount(100000),
                    bildungsKompetenzSlots: 3,
                    freizeitKompetenzSlots: 3,
                ),
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_3,
                    description: 'Deine Stiftung wird offiziell anerkannt. Du richtest Förderprogramme für politische Bildung ein, vergibst Forschungsstipendien und unterstützt lokale Demokratieprojekte. Mit einem wachsenden Team etablierst du transparente Prozesse, misst die Wirkung der Stiftungsarbeit und präsentierst Erfolge in der Öffentlichkeit. Kannst du die Reichweite und die finanzielle Basis so ausbauen, dass die Stiftung langfristig einen spürbaren Beitrag zur demokratischen Kultur leistet?',
                    investitionen: new MoneyAmount(150000),
                    bildungsKompetenzSlots: 4,
                    freizeitKompetenzSlots: 5,
                ),
            ],
        );

        $lebensziel6 = new LebenszielDefinition(
            id: LebenszielId::create(6),
            name: 'Aufbau und Leitung einer eigenen Umweltorganisation',
            description: 'Dir ist das Thema Nachhaltigkeit wichtig und du möchtest den Klimaschutz auch beruflich aktiv mitgestalten. Du bist zielstrebig und denkst innovativ. Über deinen eigenen Lebensstil hinaus willst du konkrete Beiträge zum globalen Umweltschutz leisten und neue Lösungen entwickeln.',
            phaseDefinitions: [
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_1,
                    description: 'Um deinen Lebenstraum zu verwirklichen, eignest du dir zunächst fundiertes Wissen über den Umweltschutz an. Während deines Studiums in Sustainability Science engagierst du dich außerdem ehrenamtlich in verschiedenen Umweltorganisationen und im Nachhaltigkeitsprogramm deiner Universität.',
                    investitionen: new MoneyAmount(50000),
                    bildungsKompetenzSlots: 1,
                    freizeitKompetenzSlots: 2,
                ),
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_2,
                    description: 'Nach deinem Studium startest du deine Karriere in einer internationalen Umweltorganisation. Du sammelst umfangreiche Erfahrungen und knüpfst vielfältige Kontakte. Durch verschiedene Weiterbildungen vertiefst du dein Fachwissen.',
                    investitionen: new MoneyAmount(100000),
                    bildungsKompetenzSlots: 3,
                    freizeitKompetenzSlots: 3,
                ),
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_3,
                    description: 'Du entscheidest dich, selbstständig zu werden, und gründest deine eigene Umweltorganisation, die sich für innovative Technologien einsetzt. Dabei kannst du auf dein berufliches Netzwerk zurückgreifen. Insbesondere in der Aufbauphase investierst du viel Zeit in ihre Führung. Kann sie sich langfristig erfolgreich etablieren?',
                    investitionen: new MoneyAmount(150000),
                    bildungsKompetenzSlots: 4,
                    freizeitKompetenzSlots: 5,
                ),
            ],
        );

        $lebensziel7 = new LebenszielDefinition(
            id: LebenszielId::create(7),
            name: 'Aufbau und Leitung einer Beratungsfirma für nachhaltige Unternehmensstrategien',
            description: 'Du beschäftigst dich gern mit unternehmerischen Prozessen und möchtest diese nachhaltig mitgestalten. Dir macht es Spaß, Projekte zu leiten und lösungsorientierte, realisierbare Strategien zu entwickeln. Deine organisatorischen, planerischen und betriebswirtschaftlichen Fähigkeiten unterstützen dich dabei.',
            phaseDefinitions: [
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_1,
                    description: 'Den Grundstein legst du mit einem Studium des Wirtschaftsingenieurwesens. In einem Start-up arbeitest du als Werkstudentin und sammelst erste Erfahrungen in der Beratung.',
                    investitionen: new MoneyAmount(50000),
                    bildungsKompetenzSlots: 2,
                    freizeitKompetenzSlots: 1,
                ),
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_2,
                    description: 'Nach deinem Studium bekommst du vom Start-up eine Festanstellung angeboten. Du möchtest dich intensiver mit nachhaltigen Unternehmensstrategien auseinandersetzen. Daher bildest du dich in deiner Freizeit weiter, erhältst ein Zertifikat im Nachhaltigkeitsmanagement und gründest dein eigenes Start-up für Unternehmensberatung.',
                    investitionen: new MoneyAmount(100000),
                    bildungsKompetenzSlots: 3,
                    freizeitKompetenzSlots: 3,
                ),
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_3,
                    description: 'Du berätst bereits deine ersten Stammkunden. Um dein Unternehmen weiter auszubauen, intensivierst du deine Netzwerkarbeit. Zudem besuchst du weiterhin Fortbildungen, um fachlich auf dem neuesten Stand zu bleiben. Wird sich dein Start-up dauerhaft am Markt etablieren?',
                    investitionen: new MoneyAmount(150000),
                    bildungsKompetenzSlots: 4,
                    freizeitKompetenzSlots: 5,
                ),
            ],
        );

        $lebensziel8 = new LebenszielDefinition(
            id: LebenszielId::create(8),
            name: 'Aufbau und Entwicklung eines erfolgreichen Online-Bildungsportals für berufliche Weiterbildung',
            description: 'Du vermittelst gerne Wissen, beobachtest den Arbeitsmarkt aufmerksam und erkennst Lücken in der beruflichen Weiterbildung. Deine Vision ist eine digitale Plattform, die praxisnahe Kurse, flexible Lernpfade und anerkannte Zertifikate bündelt und dabei für Berufstätige, Unternehmen und Bildungseinrichtungen gleichermaßen leicht zugänglich ist.',
            phaseDefinitions: [
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_1,
                    description: 'Du entscheidest dich für ein Studium des Bildungsmanagements und der Informatik und belegst zusätzliche Kurse zu E-Learning-Technologien. Parallel analysierst du Weiterbildungsangebote, führst Interviews mit Fachleuten aus Wirtschaft und Arbeitsverwaltung und erarbeitest ein erstes Konzept für ein modulares Bildungsportal.',
                    investitionen: new MoneyAmount(50000),
                    bildungsKompetenzSlots: 2,
                    freizeitKompetenzSlots: 1,
                ),
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_2,
                    description: 'Gemeinsam mit einem kleinen Team baust du einen Prototyp mit wenigen Pilotkursen. Erste Firmen erproben die Plattform, und ihre Rückmeldungen fließen in die Verbesserung der Benutzerfreundlichkeit und des Kursdesigns ein. Parallel möchtest du dir eine Finanzierung über Förderprogramme sichern und rechtliche Rahmenbedingungen klären.',
                    investitionen: new MoneyAmount(100000),
                    bildungsKompetenzSlots: 3,
                    freizeitKompetenzSlots: 3,
                ),
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_3,
                    description: 'Die öffentliche Version geht live. Du erweiterst das Kursportfolio, integrierst adaptive Lernpfade und baust Support-, Vertriebs- und Qualitätssicherungsstrukturen auf. Kooperationen mit Branchenverbänden und Zertifizierungsstellen erhöhen die Reichweite und das Vertrauen. Kannst du Nutzerzahlen, Kursqualität und Finanzierung so in Einklang bringen, dass dein Portal dauerhaft eine feste Größe im Weiterbildungsmarkt bleibt?',
                    investitionen: new MoneyAmount(150000),
                    bildungsKompetenzSlots: 5,
                    freizeitKompetenzSlots: 4,
                ),
            ],
        );

        $lebensziel9 = new LebenszielDefinition(
            id: LebenszielId::create(9),
            name: 'Aufbau einer weltweit erfolgreichen Fitnessmarke',
            description: 'Du liebst den Kick intensiver Workouts, experimentierst mit Ernährungstrends und teilst deine Erlebnisse gern online. Aus dieser Leidenschaft wächst der Plan, nicht nur als Coach zu arbeiten, sondern eine eigene Marke zu entwickeln, die Trainingsprogramme, Lifestyle-Produkte und Events anbietet und Menschen auf der ganzen Welt dazu motiviert, Bewegung mit Spaß zu verbinden.',
            phaseDefinitions: [
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_1,
                    description: 'Du erwirbst eine anerkannte Trainerlizenz, baust auf Social Media einen eigenen Kanal mit Workouts und Tipps auf und testest dein Konzept in lokalen Kursen. Außerdem entwickelst du ein Logo, eine Farbwelt und einen Slogan, die deiner künftigen Marke eine klare und energiegeladene Ausstrahlung verleihen.',
                    investitionen: new MoneyAmount(50000),
                    bildungsKompetenzSlots: 1,
                    freizeitKompetenzSlots: 2,
                ),
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_2,
                    description: 'Nach ersten Erfolgen im Fitnessstudio startest du eine eigene Online-Plattform mit Videokursen, Ernährungsplänen und limitierten Merchandise-Artikeln. Kooperationen mit Veranstaltern von Sportevents sollen die Reichweite erhöhen. Das Feedback aus der Community fließt direkt in neue Formate ein.',
                    investitionen: new MoneyAmount(100000),
                    bildungsKompetenzSlots: 3,
                    freizeitKompetenzSlots: 3,
                ),
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_3,
                    description: 'Die Marke gewinnt Follower auf mehreren Kontinenten. Du eröffnest Pop-up-Workouts in Metropolen, bringst eine Produktlinie für Trainingsausrüstung und Sportbekleidung heraus und schließt Franchiseverträge mit Studios im Ausland. Ziel ist es, ein globales Netzwerk aufzubauen, das Training, Lifestyle und gemeinsame Events nahtlos verbindet. Schaffst du es, die Energie der Marke weltweit lebendig zu halten?',
                    investitionen: new MoneyAmount(150000),
                    bildungsKompetenzSlots: 4,
                    freizeitKompetenzSlots: 5,
                ),
            ],
        );

        $lebensziel10 = new LebenszielDefinition(
            id: LebenszielId::create(10),
            name: 'Aufbau eines Ingenieurbüros für regionale Infrastruktur',
            description: 'Straßen, Brücken und Versorgungsnetze sind das Rückgrat jeder Region. Du möchtest dafür sorgen, dass diese Bauwerke sicher, langlebig und wirtschaftlich sind. Mit technischem Sachverstand, klaren Abläufen und verlässlicher Kommunikation baust du Schritt für Schritt ein Ingenieurbüro auf, das Kommunen und mittelständische Unternehmen bei Infrastrukturprojekten betreut.',
            phaseDefinitions: [
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_1,
                    description: 'Du studierst Bau- und Verkehrsingenieurwesen, arbeitest nebenbei als Werkstudentin im Tiefbauamt und nimmst an Fortbildungen zu Bauordnung und Vergaberecht teil. Dabei knüpfst du erste Kontakte zu Bauunternehmen, Kommunen und Fachverbänden.',
                    investitionen: new MoneyAmount(50000),
                    bildungsKompetenzSlots: 2,
                    freizeitKompetenzSlots: 1,
                ),
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_2,
                    description: 'Nach deinem Studienabschluss lässt du dich in die Ingenieurkammer eintragen und eröffnest ein eigenes Büro. Du übernimmst Vermessungen, Gutachten und Sanierungskonzepte für Gemeinden in der Region. Durch termingerechte Planung, transparente Kostenberechnungen und eine zuverlässige Baustellenbegleitung gewinnst du Vertrauen und baust ein kleines Team aus Fachkräften auf.',
                    investitionen: new MoneyAmount(100000),
                    bildungsKompetenzSlots: 4,
                    freizeitKompetenzSlots: 2,
                ),
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_3,
                    description: 'Dein Unternehmen wächst: Du betreust mehrere Landkreise, erneuerst Brücken und Radwege und richtest ein Qualitäts- und Sicherheitsmanagement ein. Gleichzeitig bildest du Nachwuchs aus und kooperierst mit Hochschulen. Gelingt es dir, Projekte termingerecht umzusetzen, das Team zu erweitern und dein Ingenieurbüro als feste Größe in der Region zu verankern?',
                    investitionen: new MoneyAmount(150000),
                    bildungsKompetenzSlots: 6,
                    freizeitKompetenzSlots: 3,
                ),
            ],
        );

        return [
            $lebensziel1,
            $lebensziel2,
            $lebensziel3,
            $lebensziel4,
            $lebensziel5,
            $lebensziel6,
            $lebensziel7,
            $lebensziel8,
            $lebensziel9,
            $lebensziel10,
        ];
    }

    public static function findLebenszielById(LebenszielId $id): LebenszielDefinition
    {
        $lebensziele = self::getAllLebensziele();
        foreach ($lebensziele as $lebensziel) {
            if ($lebensziel->id === $id) {
                return $lebensziel;
            }
        }

        throw new RuntimeException('Lebensziel ' . $id . ' not found', 1747642070);
    }

}
