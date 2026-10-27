<?php

namespace App\DataFixtures;

use App\Entity\Campagne;
use App\Entity\Cotisation;
use App\Entity\Groupement;
use App\Entity\Membre;
use App\Entity\ModeCotisation;
use App\Entity\Role;
use App\Entity\Seance;
use App\Entity\TypeCampagne;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Faker en français pour avoir des noms, adresses et téléphones réalistes
        $faker = Factory::create('fr_FR');

        // ---------- Types de campagne ----------
        $types = [];
        foreach (['Épargne', 'Tontine'] as $nomType) {
            $type = new TypeCampagne();
            $type->setNom($nomType);
            $manager->persist($type);
            $types[$nomType] = $type;
        }

        // ---------- Rôles ----------
        $roles = [];
        foreach (['Admin', 'Trésorier', 'Secrétaire', 'Membre simple'] as $nomRole) {
            $role = new Role();
            $role->setNom($nomRole);
            $manager->persist($role);
            $roles[$nomRole] = $role;
        }

        // ---------- Groupements ----------
        $nomsGroupements = ['Femmes Solidaires de Pikine', 'Union des Femmes de Thiès', 'Les Battantes de Kaolack'];

        foreach ($nomsGroupements as $nomGroupement) {
            $groupement = new Groupement();
            $groupement->setNom($nomGroupement);
            $manager->persist($groupement);

            // ---------- Membres du groupement ----------
            // La 1re est admin, la 2e trésorière, la 3e secrétaire, les autres membres simples
            $membres = [];
            for ($i = 0; $i < 8; $i++) {
                if ($i == 0) {
                    $role = $roles['Admin'];
                } elseif ($i == 1) {
                    $role = $roles['Trésorier'];
                } elseif ($i == 2) {
                    $role = $roles['Secrétaire'];
                } else {
                    $role = $roles['Membre simple'];
                }

                $membre = new Membre();
                $membre->setNomComplet($faker->firstNameFemale() . ' ' . $faker->lastName());
                $membre->setTelephone($faker->numerify('7# ### ## ##'));
                $membre->setAdresse($faker->address());
                $membre->setLogin($faker->unique()->userName());
                // Mot de passe de test "password" (haché)
                $membre->setPassword(password_hash('password', PASSWORD_DEFAULT));
                $membre->setRole($role);
                $membre->setGroupement($groupement);
                $manager->persist($membre);

                $membres[] = $membre;
            }

            // ---------- Campagnes : une épargne et une tontine par groupement ----------
            foreach ($types as $nomType => $type) {
                $dateDebut = $faker->dateTimeBetween('-6 months', '-2 months');

                $campagne = new Campagne();
                $campagne->setNom($nomType . ' ' . $dateDebut->format('Y') . ' - ' . $nomGroupement);
                $campagne->setDateDebut($dateDebut);
                $campagne->setDateFin(null); // campagne encore en cours
                $campagne->setMontant($faker->randomElement([1000, 2000, 5000, 10000]));
                $campagne->setFrequence($faker->randomElement(['hebdomadaire', 'mensuelle']));
                $campagne->setCreatedAt(new \DateTimeImmutable());
                $campagne->setTypeCampagne($type);
                $campagne->setGroupement($groupement);
                $manager->persist($campagne);

                // ---------- Affectation des membres à la campagne (ModeCotisation) ----------
                // On garde le mode de chaque membre pour savoir combien elle cotise
                $modes = [];
                foreach ($membres as $index => $membre) {
                    $mode = new ModeCotisation();
                    $mode->setCampagne($campagne);
                    $mode->setMembre($membre);
                    // Environ 1 membre sur 4 cotise librement
                    $mode->setIsLibre($faker->boolean(25));
                    $manager->persist($mode);

                    $modes[$index] = $mode;
                }

                // ---------- Séances de la campagne ----------
                for ($s = 0; $s < 4; $s++) {
                    $dateSeance = clone $dateDebut;
                    if ($campagne->getFrequence() == 'hebdomadaire') {
                        $dateSeance->modify('+' . $s . ' week');
                    } else {
                        $dateSeance->modify('+' . $s . ' month');
                    }

                    $seance = new Seance();
                    $seance->setDateSeance($dateSeance);
                    $seance->setLieu('Chez ' . $membres[$s]->getNomComplet());
                    $seance->setCreatedAt(new \DateTimeImmutable());
                    $seance->setCampagne($campagne);

                    // Pour une tontine, une membre différente reçoit l'argent à chaque séance
                    if ($nomType == 'Tontine') {
                        $seance->setBeneficiaire($membres[$s]);
                    }

                    $manager->persist($seance);

                    // ---------- Cotisations de chaque membre à cette séance ----------
                    foreach ($membres as $index => $membre) {
                        $cotisation = new Cotisation();
                        $cotisation->setSeance($seance);
                        $cotisation->setMembre($membre);

                        if ($modes[$index]->isLibre()) {
                            // Cotisation libre : montant saisi à chaque séance
                            $cotisation->setMontant($faker->randomElement([500, 1000, 1500, 2000, 3000]));
                        } else {
                            // Cotisation fixe : montant de la campagne
                            $cotisation->setMontant($campagne->getMontant());
                        }

                        $cotisation->setCreatedAt(new \DateTimeImmutable());
                        $manager->persist($cotisation);
                    }
                }
            }
        }

        $manager->flush();
    }
}
