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
            name: 'Aufbau einer Selbstversorger Farm in Kanada',
            description: 'Du suchst einen Lebensstil, der auf Nachhaltigkeit statt Konsum basiert. Am liebsten arbeitest du unter freiem Himmel,
            lernst von Pflanzen und Tieren und willst in einem Umfeld leben, das deine Werte widerspiegelt. Fernab des deutschen Konsumdrucks
            siehst du in Kanada die Chance, eine Selbstversorger-Farm aufzubauen und dich weitgehend unabhängig zu versorgen.',
            phaseDefinitions: [
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_1,
                    description: 'Um deinem nachhaltigen Traum näherzukommen, steht nun die internationale Ausbildung in ökologischer
                    Landwirtschaft an, während du parallel die Formalitäten für eine dauerhafte Aufenthaltsgenehmigung in Kanada erledigst.',
                    investitionen: new MoneyAmount( 50000),
                    bildungsKompetenzSlots: 1,
                    freizeitKompetenzSlots: 2,
                ),
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_2,
                    description: 'Nach dem Abschluss planst du, am World Wide Opportunities on Organic Farms teilzunehmen und um die Welt zu reisen.
                    Dabei möchtest du verschiedenste Selbstversorger-Projekte kennenlernen und Kontakte mit Gleichgesinnten knüpfen. Gestärkt
                    durch dieses Netzwerk wirst du dich schließlich entscheiden, in Kanada deine eigene Farm zu gründen.',
                    investitionen: new MoneyAmount(200000),
                    bildungsKompetenzSlots: 2,
                    freizeitKompetenzSlots: 4,
                ),
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_3,
                    description: 'Deine Farm läuft erfolgreich und du nimmst internationale Freiwillige auf, die deinen ressourcenschonenden Lebensstil
                    kennenlernen möchten. Durch Führungen und Vorträge verbreitet sich dein Konzept. Wird es dir gelingen, mehr Menschen für ein
                    nachhaltiges, konsumreduziertes Leben zu begeistern?',
                    investitionen: new MoneyAmount(500000),
                    bildungsKompetenzSlots: 3,
                    freizeitKompetenzSlots: 6,
                ),
            ]
        );

        $lebensziel2 = new LebenszielDefinition(
            id: LebenszielId::create(2),
            name: 'Aufforstung der Sahara in Niger',
            description: 'Du bist interessiert am Umweltschutz und beginnst bereits in der Schule dich in Umweltschutzprojekten einzubringen.
            Dir ist klar, dass du auch in deiner beruflichen Zukunft in diesem Bereich arbeiten möchtest und dort etwas bewegen möchtest.',
            phaseDefinitions: [
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_1,
                    description: 'Du bist interessiert am Umweltschutz und beginnst bereits in der Schule dich in Umweltschutzprojekten einzubringen.
                    Dir ist klar, dass du auch in deiner beruflichen Zukunft in diesem Bereich arbeiten möchtest und dort etwas bewegen möchtest.',
                    investitionen: new MoneyAmount(50000),
                    bildungsKompetenzSlots: 1,
                    freizeitKompetenzSlots: 2,
                ),
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_2,
                    description: 'Du möchtest die Leitung in dem Aufforstungsprojekt in Niger übernehmen und leitest die Freiwilligen und Angestellten
                    bei der Aufforstung an. Zukünftig sollst du Aufgaben in der Öffentlichkeitsarbeit übernehmen und dein soziales Netzwerk weiter ausbauen.',
                    investitionen: new MoneyAmount(200000),
                    bildungsKompetenzSlots: 2,
                    freizeitKompetenzSlots: 4,
                ),
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_3,
                    description: 'Um das Projekt weiter auszubauen, braucht es noch mehr Grundkapital. Du bist viel in der Welt unterwegs, um für das
                    Projekt und für den Schutz der Wälder zu werben. Kannst du das Projekt weiter ausbauen und somit noch mehr Aufforstung betreiben?',
                    investitionen: new MoneyAmount(500000),
                    bildungsKompetenzSlots: 3,
                    freizeitKompetenzSlots: 6,
                ),
            ]
        );

        $lebensziel3 = new LebenszielDefinition(
            id: LebenszielId::create(3),
            name: 'Aufbau einer Plattform für Reisecontent auf Social Media',
            description: 'Du willst nicht nur eigene Abenteuer teilen, sondern einen Ort schaffen, an dem Reisende überall auf der Welt kurze,
            packende Eindrücke, von Street-Food-Entdeckungen bis zum spontanen City-Trip, posten können. Deine Plattform soll Menschen verbinden,
            die mit wenig Planung viel erleben wollen und dabei ständig neue Impulse für das nächste Abenteuer liefern.',
            phaseDefinitions: [
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_1,
                    description: 'Begleitend zu einem Studium in Medieninformatik entwirfst du nun einen ersten Prototyp für deine Plattform:
                    Kurzvideo-Upload, Kartenansicht mit Hotspots und Chat-Funktion. Eine kleine Testgruppe soll Feedback liefern, das du direkt einarbeitest.',
                    investitionen: new MoneyAmount(50000),
                    bildungsKompetenzSlots: 1,
                    freizeitKompetenzSlots: 2,
                ),
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_2,
                    description: 'Die Plattform geht online. Du planst Promotion-Aktionen bei Hostels und auf Reisemessen zu organisieren, eigene Video-Features
                    zu produzieren und erste Mitarbeitende für Entwicklung und Community-Support einzustellen. So können Kooperationen mit Reise-Marken entstehen.
                    Zusätzlich belegst du Kurse zu Vertrags- und Steuerfragen, um die geschäftliche Seite zu meistern.',
                    investitionen: new MoneyAmount(200000),
                    bildungsKompetenzSlots: 3,
                    freizeitKompetenzSlots: 3,
                ),
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_3,
                    description: 'Deine Plattform erreicht mehrere Millionen aktive Konten. Ein interdisziplinäres Team betreut Technik, Content-Moderation und
                    Partner-Management. Währenddessen  priorisiert du neue Funktionen, wie etwa Live-Routen oder Gruppen-Challenges. Jetzt gilt es, den frischen
                    Ideenfluss hochzuhalten und die Plattform stabil weiterzuentwickeln.',
                    investitionen: new MoneyAmount(500000),
                    bildungsKompetenzSlots: 4,
                    freizeitKompetenzSlots: 5,
                ),
            ]
        );

        $lebensziel4 = new LebenszielDefinition(
            id: LebenszielId::create(4),
            name: 'Aufbau einer renommierten Anwaltskanzlei',
            description: 'Gerechtigkeit, Verlässlichkeit und die Bewahrung bewährter Strukturen prägen dein Denken. Du fühlst dich in der Welt der Paragrafen wohl,
            verfolgst politische Entscheidungsprozesse aufmerksam und verhandelst gerne belastbare Kompromisse. Dein Ziel ist eine Kanzlei, die für fachliche Exzellenz,
            klare Prinzipien und höchste Servicequalität steht - ein verlässlicher Anlaufpunkt für Mandantschaft und Team gleichermaßen.',
            phaseDefinitions: [
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_1,
                    description: 'Du beginnst das Jurastudium, vertiefst dich in Staats-, Zivil- und Wirtschaftsrecht und sammelst erste Einblicke durch Praktika bei
                    Gerichten, Ministerien und Kanzleien. Dabei klärst du für dich, welche Rechtsgebiete deine zukünftige Kanzlei abdecken soll.',
                    investitionen: new MoneyAmount(50000),
                    bildungsKompetenzSlots: 2,
                    freizeitKompetenzSlots: 1,
                ),
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_2,
                    description: 'Nun stehen das Schwerpunktstudium, das Erste Staatsexamen und das Referendariat an, was dich fachlich voranbringen soll. Parallel
                    planst du, dein Netzwerk in Anwaltskammern und Fachverbänden auszubauen, Rhetorik- sowie Verhandlungsseminare zu besuchen und Gesetzesreformen zu
                    verfolgen, um auf dem neuesten Stand zu bleiben.',
                    investitionen: new MoneyAmount(200000),
                    bildungsKompetenzSlots: 4,
                    freizeitKompetenzSlots: 2,
                ),
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_3,
                    description: 'Nach dem Zweiten Staatsexamen gewinnst du erste eigene Mandate, sammelst Berufserfahrung und führst komplexe Fälle. Mit einem stabilem
                    Netzwerk und nachweislichen Erfolgen gründest du deine Kanzlei, stellst qualifiziertes Personal ein und etablierst klare Qualitätsstandards. Gelingt
                    es dir, Mandatszahlen und Reputation kontinuierlich zu steigern und die Kanzlei zu einer festen Größe am Markt zu entwickeln?',
                    investitionen: new MoneyAmount(500000),
                    bildungsKompetenzSlots: 6,
                    freizeitKompetenzSlots: 3,
                ),
            ]
        );

        $lebensziel5 = new LebenszielDefinition(
            id: LebenszielId::create(5),
            name: 'Aufbau einer Stiftung zur Förderung der Demokratie',
            description: 'Du engagierst dich leidenschaftlich für demokratische Werte und gesellschaftlichen Zusammenhalt. Anstatt nur einzelne Projekte zu unterstützen,
            willst du eine dauerhafte Institution schaffen – eine Stiftung, die Bildungsangebote, Forschung und zivilgesellschaftliche Initiativen für mehr Teilhabe
            fördert. Dein Ziel ist es, langfristig Strukturen zu stärken, die faire Mitsprache und Chancengleichheit sichern.',
            phaseDefinitions: [
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_1,
                    description: 'Du startest ein Studium in Politikwissenschaft und Verwaltung und absolvierst Praktika bei Nichtregierungsorganisationen (NGOs) sowie
                    Stiftungen. Parallel baust du einen Podcast auf, in dem du aktuelle Demokratiethemen analysierst und erste Kontakte in Wissenschaft, Medien und
                    Zivilgesellschaft knüpfst.',
                    investitionen: new MoneyAmount(50000),
                    bildungsKompetenzSlots: 2,
                    freizeitKompetenzSlots: 1,
                ),
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_2,
                    description: 'Nach dem Abschluss arbeitest du in einer gemeinnützigen Organisation und leitest kleinere Projekte, in denen du Fundraising-Methoden
                    kennenlernst. Du möchtest nun die Satzung deiner künftigen Stiftung entwerfen, rechtliche Rahmenbedingungen klären, einen Beirat zusammenstellen
                    und wirbst Startkapital bei Förderinstituten ein.',
                    investitionen: new MoneyAmount(200000),
                    bildungsKompetenzSlots: 3,
                    freizeitKompetenzSlots: 3,
                ),
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_3,
                    description: 'Deine Stiftung wird offiziell anerkannt. Du richtest Förderprogramme für politische Bildung ein, vergibst Forschungsstipendien und
                    unterstützt lokale Demokratie-Projekte. Mit einem wachsenden Team etablierst du transparente Prozesse, misst Wirkung und präsentierst Erfolge
                    in der Öffentlichkeit. Kannst du Reichweite und finanzielle Basis so ausbauen, dass die Stiftung langfristig einen spürbaren Beitrag zur
                    demokratischen Kultur leistet?',
                    investitionen: new MoneyAmount(500000),
                    bildungsKompetenzSlots: 4,
                    freizeitKompetenzSlots: 5,
                ),
            ]
        );

        $lebensziel6 = new LebenszielDefinition(
            id: LebenszielId::create(6),
            name: ' Umweltorganisation gründen und erfolgreich leiten',
            description: 'Dir ist das Thema Nachhaltigkeit wichtig und du möchtest auch in deiner beruflichen Zukunft den Klimaschutz aktiv mitgestalten. Du bist
            zielstrebig und denkst innovativ. Du möchtest über deinen nachhaltigen Lebensstil hinaus konkrete Beiträge und Lösungen für den globalen Umweltschutz erschaffen.',
            phaseDefinitions: [
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_1,
                    description: 'Damit du deinen Lebenstraum erfüllen kannst, ist es wichtig, dir zuerst Wissen im Bereich Umweltschutz anzueignen. Auch während deines Studiums
                    im Bereich Sustainability Science willst du ehrenamtlich in verschiedenen Umweltorganisationen aktiv sein und engagierst dich in dem Nachhaltigkeitsprogramm deiner Uni.',
                    investitionen: new MoneyAmount(50000),
                    bildungsKompetenzSlots: 1,
                    freizeitKompetenzSlots: 2,
                ),
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_2,
                    description: 'Nach deinem Studium startest du deine berufliche Karriere in einer internationalen Umweltorganisation. Du möchtest nun viel Erfahrung sammeln und knüpfst
                    unterschiedliche Kontakte. Mit verschiedenen Weiterbildungen strebst du an, dein Wissen zu vertiefen.',
                    investitionen: new MoneyAmount(200000),
                    bildungsKompetenzSlots: 3,
                    freizeitKompetenzSlots: 3,
                ),
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_3,
                    description: 'Du entscheidest, dich selbständig zu machen und gründest deine eigene Umweltorganisation, die sich für innovative Technologien einsetzt. Dabei
                    kannst du auf dein soziales Netzwerk zurückgreifen. Insbesondere in der Aufbauphase investierst du viel Freizeit in die Führung der Umweltorganisation. Kann deine
                    Umweltorganisation langfristig überleben?',
                    investitionen: new MoneyAmount(500000),
                    bildungsKompetenzSlots: 4,
                    freizeitKompetenzSlots: 5,
                ),
            ]
        );

        $lebensziel7 = new LebenszielDefinition(
            id: LebenszielId::create(7),
            name: 'Leitung und Weiterentwicklung einer Beratungsfirma für nachhaltige Unternehmensstrategie',
            description: 'Du beschäftigst dich gerne mit unternehmerischen Prozessen und möchtest diese nachhaltig mitgestalten. Dir macht es Spaß Projekte
            zu leiten und lösungsorientierte, realisierbare Strategien zu entwickeln. Deine Fähigkeiten in Organisation, Planung und Management unterstützen dich dabei.',
            phaseDefinitions: [
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_1,
                    description: 'Damit du deinen Lebenstraum erfüllen kannst, startest du mit einem Studium im Bereich Wirtschaftsingenieurwesen. In einem
                    Start-Up-Unternehmen wirst du als Werkstudentin angestellt und sammelst im Beratungsbereich erste Erfahrungen.',
                    investitionen: new MoneyAmount(50000),
                    bildungsKompetenzSlots: 2,
                    freizeitKompetenzSlots: 1,
                ),
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_2,
                    description: 'Nach deinem Studium bekommst du von dem Start-Up eine Festanstellung angeboten. Du möchtest dich zusätzlich stärker mit nachhaltigen
                    Unternehmensstrategien auseinandersetzten. Daher bildest du dich nun in deiner Freizeit selbst weiter, erhältst ein Zertifikat im Bereich
                    Nachhaltigkeitsmanagement und gründest dein eigenes Start-Up für Unternehmensberatung.',
                    investitionen: new MoneyAmount(200000),
                    bildungsKompetenzSlots: 3,
                    freizeitKompetenzSlots: 3,
                ),
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_3,
                    description: 'Du berätst bereits deine ersten Stammkunden in deinem Start-Up. Um dein Start-Up langfristig noch weiter auszubauen, ist viel
                    Netzwerkarbeit gefragt. Zudem besuchst du weiterhin Fortbildungen, um immer auf dem aktuellen Stand zu bleiben. Wird dein Start-Up langfristig überleben?',
                    investitionen: new MoneyAmount(500000),
                    bildungsKompetenzSlots: 4,
                    freizeitKompetenzSlots: 5,
                ),
            ]
        );

        $lebensziel8 = new LebenszielDefinition(
            id: LebenszielId::create(8),
            name: 'Aufbau und Entwicklung eines erfolgreichen Online-Bildungsportals für berufliche Weiterbildung',
            description: 'Du vermittelst gerne Wissen, beobachtest den Arbeitsmarkt aufmerksam und erkennst Lücken in der beruflichen Weiterbildung. Deine Vision ist eine digitale
            Plattform, die praxisnahe Kurse, flexible Lernpfade und anerkannte Zertifikate bündelt und dabei leicht zugänglich für Berufstätige, Unternehmen und Bildungseinrichtungen gleichermaßen ist.',
            phaseDefinitions: [
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_1,
                    description: 'Du entscheidest dich für ein Studium in Bildungsmanagement und Informatik und belegst zusätzliche Kurse zu E-Learning-Technologien. Parallel analysierst du
                    Weiterbildungsangebote, führst Interviews mit Fachleuten aus Wirtschaft und Arbeitsverwaltung und erarbeitest ein erstes Konzept für ein modulares Online-Portal.',
                    investitionen: new MoneyAmount(50000),
                    bildungsKompetenzSlots: 2,
                    freizeitKompetenzSlots: 1,
                ),
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_2,
                    description: 'Gemeinsam mit einem kleinen Team baust du einen Prototypen mit wenigen Pilotkursen. Erste Firmen erproben die Plattform und ihre Rückmeldungen
                    fließen in Usability-Verbesserungen und Kursdesign ein. Parallel möchtest du dir Finanzierung über Förderprogramme sichern und rechtliche Rahmenbedingungen aufstellen.',
                    investitionen: new MoneyAmount(200000),
                    bildungsKompetenzSlots: 3,
                    freizeitKompetenzSlots: 3,
                ),
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_3,
                    description: 'Die öffentliche Version geht live. Du erweiterst das Kursportfolio, integrierst adaptive Lernpfade und baust Support-, Vertrieb- und Qualitätssicherungsstrukturen
                    auf. Kooperationen mit Branchenverbänden und Zertifizierungsstellen erhöhen Reichweite und Vertrauen. Kannst du Nutzerzahlen, Kursqualität und Finanzierung so in Einklang
                    bringen, dass dein Portal dauerhaft eine feste Größe im Weiterbildungsmarkt bleibt?',
                    investitionen: new MoneyAmount(500000),
                    bildungsKompetenzSlots: 5,
                    freizeitKompetenzSlots: 4,
                ),
            ]
        );

        $lebensziel9 = new LebenszielDefinition(
            id: LebenszielId::create(9),
            name: 'Aufbau einer weltweit erfolgreichen Fitnessmarke',
            description: 'Du liebst den Kick intensiver Workouts, experimentierst mit Ernährungstrends und teilst deine Erlebnisse gern online. Aus dieser Leidenschaft wächst der Plan,
            nicht nur als Coach zu arbeiten, sondern eine eigene Marke zu entwickeln. Trainingsprogramme, Lifestyle-Produkte und Events, die Menschen überall motivieren, Bewegung mit Spaß zu verbinden.',
            phaseDefinitions: [
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_1,
                    description: 'Du absolvierst eine anerkannte Trainerlizenz, baust auf Social Media einen ersten Kanal mit Workouts sowie Tipps auf und testest dein Konzept in lokalen Kursen.
                    Logo, Farbwelt und Slogan der künftigen Marke mit klarer und energiegeladener Ausstrahlung sollen entstehen.',
                    investitionen: new MoneyAmount(50000),
                    bildungsKompetenzSlots: 1,
                    freizeitKompetenzSlots: 2,
                ),
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_2,
                    description: 'Nach ersten Erfolgen im Fitnessstudio startest du eine eigene Online-Plattform mit Videokursen, Ernährungsplänen und limitierten Merchandise-Artikel.
                    Kooperationen mit Sportevents sollen nun für Reichweite sorgen und Feedback aus der Community direkt in neue Formate einfließen.',
                    investitionen: new MoneyAmount(200000),
                    bildungsKompetenzSlots: 3,
                    freizeitKompetenzSlots: 3,
                ),
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_3,
                    description: 'Die Marke gewinnt Follower auf mehreren Kontinenten. Du eröffnest Pop-up-Workouts in Metropolen, bringst eine Produktlinie für Equipment und Sport-Fashion
                    heraus und schließt Franchise-Verträge mit Studios im Ausland. Ziel ist es, ein globales Netzwerk aufzubauen, das Training, Lifestyle und Event-Erlebnis nahtlos verbindet.
                    Schaffst du es, die Energie der Marke weltweit lebendig zu halten?',
                    investitionen: new MoneyAmount(500000),
                    bildungsKompetenzSlots: 4,
                    freizeitKompetenzSlots: 5,
                ),
            ]
        );

        $lebensziel10 = new LebenszielDefinition(
            id: LebenszielId::create(10),
            name: 'Aufbau eines Ingenieurbüros für regionale Infrastruktur',
            description: 'Straßen, Brücken und Versorgungsnetze sind das Rückgrat jeder Region. Du möchtest dafür sorgen, dass diese Bauwerke sicher, langlebig und wirtschaftlich sind. Mit
            technischem Sachverstand, klaren Abläufen und verlässlicher Kommunikation baust du Schritt für Schritt ein Planungsbüro auf, das Kommunen und mittelständische Unternehmen bei
            Infrastrukturprojekten betreut.',
            phaseDefinitions: [
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_1,
                    description: 'Du studierst Bau- und Verkehrsingenieurwesen, arbeitest nebenbei als Werkstudent im Tiefbauamt und nimmst an Fortbildungen zu Bauordnung und
                    Vergaberecht teil. Erste Kontakte zu Bauunternehmen, Kommunen und Fachverbänden entstehen.',
                    investitionen: new MoneyAmount(50000),
                    bildungsKompetenzSlots: 2,
                    freizeitKompetenzSlots: 1,
                ),
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_2,
                    description: 'Mit deinem erfolgreich abgeschlossenen Studium lässt du dich in die Ingenieurkammer eintragen und eröffnest nun ein kleines Büro. Du übernimmst
                    Vermessungen, Gutachten und Sanierungskonzepte für Gemeinden im Umkreis. Durch termingerechte Planung, transparente Kostenberechnungen und Baustellenbegleitung
                    gewinnst du Vertrauen und möchtest ein kleines Team aus Fachkräften aufbauen. ',
                    investitionen: new MoneyAmount(200000),
                    bildungsKompetenzSlots: 4,
                    freizeitKompetenzSlots: 2,
                ),
                new LebenszielPhaseDefinition(
                    lebenszielPhaseId: LebenszielPhaseId::PHASE_3,
                    description: 'Dein Unternehmen wächst: Du betreust mehrere Landkreise, erneuerst Brücken und Radwege und richtest ein Qualitäts- und Sicherheitsmanagement ein.
                    Gleichzeitig bildest du Nachwuchs aus und kooperierst mit Hochschulen. Gelingt es dir, Projekte termingerecht umzusetzen, das Team zu erweitern und deine
                    Planungsgesellschaft als feste Größe in der Region zu verankern?',
                    investitionen: new MoneyAmount(500000),
                    bildungsKompetenzSlots: 6,
                    freizeitKompetenzSlots: 3,
                ),
            ]
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
