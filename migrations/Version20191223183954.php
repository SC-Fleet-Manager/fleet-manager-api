<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20191223183954 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add ship_name and ship_chassis tables.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE ship_name (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', my_hangar_name VARCHAR(255) NOT NULL, ship_matrix_name VARCHAR(255) NOT NULL, INDEX my_hangar_name_idx (my_hangar_name), INDEX ship_matrix_name_idx (ship_matrix_name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ship_chassis (id CHAR(36) NOT NULL COMMENT \'(DC2Type:uuid)\', rsi_id INT NOT NULL, name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_3BE443B2967433DD (rsi_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->addSql("INSERT INTO ship_chassis(id, rsi_id, name) VALUES
            ('6da961d9-cfb8-453b-aa78-14e1ab0a15ce', 0, 'Greycat'),
            ('14fc3970-1f83-43f1-b7e0-9e271c27c762', 1, 'Aurora'),
            ('b203b6eb-ff97-42d2-8131-83bf10db2303', 2, '300'),
            ('4f56efee-a46f-4922-9293-8558236a6236', 3, 'Hornet'),
            ('ff11608d-f2c4-4a47-94bb-dee1928998ed', 4, 'Constellation'),
            ('a606c175-07e8-42df-9250-8c90eee094a5', 5, 'Freelancer'),
            ('0e1fbad1-80d2-4855-8769-8b587797bfe6', 6, 'Cutlass'),
            ('9543cdd4-359c-40b2-8564-8c4fd096e655', 7, 'Avenger'),
            ('03e27686-8c62-451b-8cee-e69912c80004', 8, 'Gladiator'),
            ('79771b16-b16c-4e25-a6c2-2039e03cc1d1', 9, 'M50'),
            ('603fcd48-0594-457d-ba83-02357d86399e', 10, 'Starfarer'),
            ('c0bbed23-826c-4c94-8396-c3b1965f6efd', 11, 'Caterpillar'),
            ('0479698a-1b1c-4937-b838-024192877c41', 12, 'Retaliator'),
            ('05f4fc63-728c-4acb-8d4a-ae54ecc0c63a', 13, 'Scythe'),
            ('e13f571e-5404-46f4-98b2-d56402846e39', 14, 'Idris'),
            ('1f7a8e65-faa2-4779-bf43-acb102d6c311', 15, 'Merlin'),
            ('7109b4ef-8b74-4c68-bf8b-ab08b6170ee2', 16, 'Mustang'),
            ('b54ec65a-8e64-4a35-9e9b-c670fd248607', 17, 'Redeemer'),
            ('b6986f1d-3bae-4413-827a-56d9c3344955', 18, 'Gladius'),
            ('32570b09-245c-45e0-80fb-0ee9e9a8cfa2', 19, 'Khartu'),
            ('eec1f2f6-2e5d-4df4-acb6-482b21af84d9', 20, 'Merchantman'),
            ('cf555099-3e86-4dd3-b3f9-800ccd54d3d5', 21, '890 Jump'),
            ('02cd2db3-c89a-4088-bf2a-2108b91627a3', 22, 'Carrack'),
            ('232bb649-c3c5-4d6c-b946-40f059e3a78f', 23, 'Herald'),
            ('64eafe24-ecad-4b8c-b286-693e50bf81cc', 24, 'Hull'),
            ('090cc080-ce2e-4eb4-ac22-1eb1cd7135f9', 25, 'Orion'),
            ('f1763d1d-d92b-4ed8-90a0-33bd40551e88', 26, 'Reclaimer'),
            ('fe715515-3225-4929-91b4-a04f67236c92', 28, 'Javelin'),
            ('cb04aa71-7f1b-4ee5-9f6b-3381d059c61b', 30, 'Vanguard'),
            ('830a04ef-a368-4e60-85ea-98da73abb3f7', 31, 'Reliant'),
            ('f63f42ac-20e8-490e-83af-b76b8289db66', 32, 'Starliner'),
            ('3fc36d5d-5451-4b44-9a59-9adb9fa9d677', 33, 'Glaive'),
            ('31c6cd10-2a02-4d21-a02d-2307b7ac19ce', 34, 'Endeavor'),
            ('3dd544ce-c026-478f-ae0e-26c36c20ce16', 35, 'Sabre'),
            ('d11e34d7-dd3d-46e6-a0c5-341fbfd96005', 37, 'Crucible'),
            ('636c2a1d-01bf-47e9-ae05-7c6275166853', 38, 'P72 Archimedes'),
            ('e4ea2ed4-ba7d-4351-9aaa-162820938118', 39, 'Blade'),
            ('ec96956e-53f8-4a98-a854-224bc98e61b0', 40, 'Prospector'),
            ('b950529e-0544-48af-929a-7e99024cc673', 41, 'Buccaneer'),
            ('509ff09d-e166-42c9-899a-d0fcdf5afdb9', 42, 'Dragonfly'),
            ('67a36f65-a21f-481a-9802-611d56afd094', 43, 'MPUV'),
            ('e332b1a5-9cec-4bbf-9b9e-8b6ee65af380', 44, 'Terrapin'),
            ('37e5dd2f-06ff-4d43-bb3d-98128fda4c9e', 45, 'Polaris'),
            ('aeacc3f4-bdb3-4843-a673-cd504c20eec8', 46, 'Prowler'),
            ('a578fad0-1a7f-42ad-b6ec-ed056b7777ed', 47, '85X'),
            ('7fd03d81-54c2-4a4c-916b-cf61f12fd05c', 48, 'Razor'),
            ('1f026856-bcf9-4972-9120-babbf87784b7', 49, 'Hurricane'),
            ('6b0b5cda-c4a9-4e87-9024-8c154f76bf77', 50, 'Defender'),
            ('d811f2ee-6c7f-453b-81a2-6b76ef62a7eb', 51, 'Eclipse'),
            ('58b17b38-477f-4f3b-9449-5ae851aa2418', 52, 'Nox'),
            ('c694d707-0de3-4d86-b97a-4823054349b5', 53, 'Cyclone'),
            ('4e29271c-23f4-4f29-a38e-d4972049a4ca', 54, 'Ursa'),
            ('74351071-c434-4dc3-a83e-f81d88615ce6', 55, '600i'),
            ('9a8d1089-457e-475e-9768-1948db061534', 56, 'X1'),
            ('c58e1886-993b-4f97-b9f2-91609c0f5f60', 57, 'Pioneer'),
            ('1a6401cd-f338-4378-9983-8d45b452db5c', 58, 'Hawk'),
            ('6da39c0c-5f3c-4cdb-914d-b5d9dfc69812', 59, 'Hammerhead'),
            ('c7a20b5a-f047-41d6-b4dd-94e329024ba2', 60, 'Planetary Beacon'),
            ('c8eecd7f-3903-4df0-8780-a5045e4d7aaf', 61, 'Nova'),
            ('1ce4a597-4a28-40a9-a498-b0441859d491', 62, 'Vulcan'),
            ('f9e13fca-d5b2-4594-baa3-fa179337b858', 63, '100'),
            ('7c270d28-69e3-43d2-9fe8-89af75e93713', 64, 'Starlifter'),
            ('3329e2cb-8de1-4725-819e-07df8c4acb12', 65, 'Vulture'),
            ('b62f04c8-4568-466e-877d-9b8104ff6c3f', 66, 'Apollo'),
            ('145d8a54-16ca-4534-99fd-c85ce3d4c000', 67, 'Mercury Star Runner'),
            ('9e40469a-d336-49e2-ae3c-5bef823048f4', 68, 'Valkyrie'),
            ('dfa09c7d-6cbe-4ccf-8747-020ab44ba6e8', 69, 'Kraken'),
            ('f883a29c-a631-4628-90b8-ea964c27fa4e', 70, 'Arrow'),
            ('8959eaab-3f05-4e2d-9dfc-c4f3f4adf3a1', 71, 'San\'tok.yāi'),
            ('5191b5f4-776e-4d97-8313-2ea0dbd75529', 72, 'SRV'),
            ('404a4f1c-914a-429a-b9ff-da5069302957', 73, 'Corsair'),
            ('e09318c9-13ce-4d53-87dd-6be41383d3b6', 74, 'Ranger'),
            ('89d14a43-d75b-42d8-9a85-ccac9580bf59', 75, 'Ballista'),
            ('d72b39f9-7219-4088-bd3f-760c3e7273d4', 76, 'Nautilus'),
            ('ba6f5cae-a46d-47cb-bec8-f747f5941410', 77, 'Mantis'),
            ('4b54ff15-1158-4e44-a0b6-e9eca02cae67', 78, 'Pisces'),
            ('efe4c440-f9b3-4b36-905c-19b7cdbc8300', 79, 'Ares'),
            ('11862308-192f-4934-b1f4-ea46e4e8e54d', 80, 'Mole'),
            ('f40ab3b3-7314-4644-9eb4-ded65a7dad78', 1001, 'F8C');
        ");

        $this->addSql("INSERT INTO ship_name(id, my_hangar_name, ship_matrix_name) VALUES
            ('b0193c66-ddc1-450c-93df-14475642de68', '315p Explorer', '315p'),
            ('3e7fa1d8-6fdd-4489-8b2e-fb631d53db0f', '325a Fighter', '325a'),
            ('df75474b-3a01-4c0d-9bac-7ee1e494f518', '350r Racer', '350r'),
            ('4dfc86a0-c5f5-4a18-8a88-a7d6f259015b', '600i Exploration Module', '600i Explorer'),
            ('90bebdb2-b6e7-4482-8776-82afe724f33c', '600i Touring Module', '600i Touring'),
            ('3c155248-3168-478c-8e61-0c7b95e9a044', '890 JUMP', '890 Jump'),
            ('b7694c51-01fe-45ee-9cf0-73e63e63a10b', 'Aopoa San\'tok.yāi', 'San\'tok.yāi'),
            ('31b377c8-b35d-4d4e-860d-2a95c362d4de', 'Argo SRV', 'SRV'),
            ('881c6a9e-bd6b-4b31-8fad-46ee3600fb24', 'Argo Mole - Carbon Edition', 'Argo Mole Carbon Edition'),
            ('bbb7ff13-d2cf-4d92-aae0-99c8700e8dc6', 'Argo Mole - Talus Edition', 'Argo Mole Talus Edition'),
            ('f415a2f3-b049-4873-baeb-f6b4abbdad8a', 'Ballista', 'Anvil Ballista'),
            ('ec5af854-0bc0-46d7-893e-d67b96c0646f', 'Ballista Snowblind', 'Anvil Ballista Snowblind'),
            ('fd75f9ed-89cc-4a18-bce9-4576ea499ea7', 'Ballista Dunestalker', 'Anvil Ballista Dunestalker'),
            ('b5bf171c-c83b-4d35-baa5-2ef0e3366a2b', 'Pisces', 'C8 Pisces'),
            ('48023bb2-35bf-4ea8-8630-cea229a0e5c3', 'Pisces - Expedition', 'C8X Pisces Expedition'),
            ('b1d87e69-9a9e-4578-a35f-ffdbb5f9766a', 'Consolidated Outland Pioneer', 'Pioneer'),
            ('28cb63c0-f15f-47d7-a027-afd4e0bbf2ff', 'Crusader Mercury Star Runner', 'Mercury Star Runner'),
            ('d978e2da-79f5-4ac3-a044-0e346bc12d50', 'Cyclone RC', 'Cyclone-RC'),
            ('9638a1f3-a968-4d6a-b1aa-5cbbe2bde9e5', 'Cyclone RN', 'Cyclone-RN'),
            ('8f67536c-a628-4232-9288-e63e74a63103', 'Cyclone-TR', 'Cyclone-TR'),
            ('5d944e62-06b8-4235-a72d-ba09d6ad1b1c', 'Cyclone AA', 'Cyclone-AA'),
            ('f9cce998-39fb-45a3-b213-954891a3a3db', 'Defender', 'Banu Defender'),
            ('ecdc7de4-1bee-4114-aeee-881e39d60530', 'Hercules Starlifter C2', 'C2 Hercules'),
            ('0b91f677-fe52-4641-842d-143d6e98f7c1', 'Hercules Starlifter M2', 'M2 Hercules'),
            ('fb521768-70fe-4729-b9d3-9af1d020c291', 'Hercules Starlifter A2', 'A2 Hercules'),
            ('21f2a893-d8d5-4a80-840c-e655619b3cb2', 'Hornet F7C', 'F7C Hornet'),
            ('4b125230-c256-488a-af5e-cd1f10cb1cf8', 'Hornet F7C-M Heartseeker', 'F7C-M Super Hornet Heartseeker'),
            ('d98414e5-caff-4dd2-9daf-f635b0184fad', 'Idris-P Frigate', 'Idris-P'),
            ('a0d60fe8-54ff-4a7b-ac2f-32527f7c3db9', 'Idris-M Frigate', 'Idris-M'),
            ('359e76a3-7c63-4b3e-9d38-fa3bc2d8f457', 'Khartu-al', 'Khartu-Al'),
            ('dbd145f0-5e32-49e5-976e-96e7eba6e7f6', 'Nova Tank', 'Nova'),
            ('9966a7f2-836d-412d-8f3e-e7e4b8a77be0', 'P-72 Archimedes', 'P72 Archimedes'),
            ('92740f1b-3f50-48b6-931e-57e1ee14489d', 'Reliant Kore - Mini Hauler', 'Reliant Kore'),
            ('8cdf6b8b-e065-4363-b510-06a0a22b0ab6', 'Reliant Mako - News Van', 'Reliant Mako'),
            ('371904a1-8f15-47f4-939e-5a9301f04a7c', 'Reliant Sen - Researcher', 'Reliant Sen'),
            ('f56560e7-bd5d-466f-952f-b807729e961a', 'Reliant Tana - Skirmisher', 'Reliant Tana'),
            ('45554f85-7835-4a18-890e-6498b99c8003', 'Valkyrie', 'Valkyrie'),
            ('37b11481-24e1-424d-a064-f2b2273067a0', 'Valkyrie Liberator Edition', 'Valkyrie Liberator Edition'),
            ('c58502fe-4722-4d17-9c59-43bf1049279b', 'X1', 'X1 Base'),
            ('3ece42c7-56ea-4ab5-b6cb-af6e5991ec09', 'X1 - FORCE', 'X1 Force'),
            ('115ec35e-b604-427f-85b9-bd295e11a3a9', 'X1 - VELOCITY', 'X1 Velocity'),
            ('881210ee-95f0-4a9a-82c5-ded943bec8b9', 'Cutlass 2949 Best In Show', 'Cutlass Black Best In Show Edition'),
            ('2c880588-90a3-4fbd-b9a2-6adc0fdeb712', 'Caterpillar 2949 Best in Show', 'Caterpillar Best In Show Edition'),
            ('c07a8164-70cb-47d0-b114-e4b6f1dc44ec', 'Hammerhead 2949 Best in Show', 'Hammerhead Best In Show Edition'),
            ('aa89b4ad-c3e7-4765-ba04-574d3ff30c67', 'Reclaimer 2949 Best in Show', 'Reclaimer Best In Show Edition');
        ");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE ship_name');
        $this->addSql('DROP TABLE ship_chassis');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
