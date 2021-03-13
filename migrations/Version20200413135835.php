<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20200413135835 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Change ship names.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX my_hangar_name_idx ON ship_name');
        $this->addSql('DROP INDEX ship_matrix_name_idx ON ship_name');
        $this->addSql('ALTER TABLE ship_name DROP my_hangar_name, DROP ship_matrix_name');
        $this->addSql('DELETE FROM ship_name');

        $this->addSql("INSERT INTO ship_name(id, my_hangar_name_pattern, provider_id) VALUES
            ('1b532ad4-e42b-4e0c-af15-24a5477124bd', '^350r racer$', 'a9a273c4-6a99-472d-a2b7-2906114a93bf'),
            ('345dee13-5388-4008-abf5-60175becc147', '^325a fighter$', '493dfa8c-2f6f-46ac-a894-b8f23601159e'),
            ('6a976ffd-8940-4658-bf27-4cd98d0062f4', '^600i( Exploration Module)?$', '8bdd0894-3aca-43b0-b617-aaf02b02ecae'),
            ('524ce63f-7359-47dd-9ba5-a8d3486c8a11', '^600i Touring Module$', '0d6fc4f8-acae-46a7-9a19-a744b6f9ce32'),
            ('c6f71b8f-69d1-4e3a-b0b4-884545a8a765', '^(aopoa )?san\'tok.yāi$', 'b279137c-c453-437c-9254-b4527188b048'),
            ('589e286c-533f-4352-907f-36e758406d39', '^argo mole$', '8bfb1347-9d7f-4a74-aacd-ed33a40ca808'),
            ('513d3d3c-8dca-4a37-9c8f-62f76f1a3b52', '^argo mole - carbon edition$', '3dfde416-7cd3-4b4b-b304-37b0cb7b50fb'),
            ('ed6a9a7a-c8eb-4b1b-b38e-9f14baa83811', '^argo mole - talus edition$', '8cd4750a-8e0b-44d6-898f-2f7061282ff0'),
            ('fa0d65d6-368a-4d88-8bc1-6ddf9f8ea98b', '^argo srv$', '637ed4a7-a97d-43b4-a63f-a470b1b62daa'),
            ('53223a6a-e167-42ff-9c68-50c00e87bbcd', '^(vanduul )?blade$', 'c49d6010-8f15-46bd-8bc1-9193750340a6'),
            ('a99189d4-0cb9-4845-980f-962db1dabd48', '^captured vanduul scythe$', 'efbe3bf7-c120-45b1-afa2-709982fa4aaf'),
            ('368cc2dc-985f-40ab-b8f3-1927ad88cf0a', '^carrack expedition with pisces expedition$', '51397fae-3909-4fd8-a7e3-4eacf35c2cba'),
            ('589ac046-83aa-42a1-9d4b-3f5536743682', '^carrack with pisces expedition$', '2bd1f2a4-e9a9-450b-972f-e147c3106881'),
            ('9d5f52cc-c79e-4c0d-817d-66fb732199cb', '^caterpillar 2949 best in show$', 'e78ebb5f-8035-4b42-899f-96ec993087c0'),
            ('48edd042-5d28-4d90-8245-aca80f7d8076', '^caterpillar pirate edition$', 'eeb1f901-f09c-463f-8704-ba5c87a4a13f'),
            ('1defd7e1-9121-4d46-a4b3-a526a247fa0f', '^consolidated outland pioneer$', 'fbb58296-a65b-43a3-9222-8da823375b0b'),
            ('435c96b0-ae95-49d4-91c5-dc5150a11bca', '^constellation phoenix emerald$', '51480c7f-286e-4aed-aaa7-3a005e7b6276'),
            ('57466a9c-3bed-4d92-8af2-4114992a7b03', '^crusader ares inferno$', '604a9148-9270-4acb-bf3e-6dfb6d37aba1'),
            ('2529dc17-1932-4528-b152-860b47eb1cc4', '^crusader ares ion$', '07ea9bca-7dca-4672-9990-6209702359a7'),
            ('15a4441b-796f-4e94-971d-e0ef95148565', '^crusader hercules starlifter a2$', '2a00cc23-3c1f-44ed-adc3-2e8d1e80a696'),
            ('47251635-a3d4-4176-b313-3663741b32b8', '^crusader mercury star runner$', 'becb68cc-eae3-40b9-9300-150d2a61a671'),
            ('d3efcc29-0b33-4571-977f-08e39b8acc9d', '^cutlass 2949 best in show$', 'fa73cc92-b4c5-407d-aafb-70df88b7ff2f'),
            ('2866de89-20b1-4de9-b67f-8aff10e22015', '^cyclone aa$', '40b9f8ca-c325-4295-a61c-2b2e1ba9212f'),
            ('20e188f3-fd3b-4a4b-94b1-4c4656cfef70', '^cyclone rc$', '13145fb5-ddb4-40d6-a418-b984887726d7'),
            ('71b66251-57f9-4cda-9eae-449e2bcc629d', '^cyclone rn$', '411368d0-42fa-4638-8c3f-bc3f03daf75d'),
            ('6004cfee-f0dd-4513-8dba-6a672c67bfa9', '^dragonfly star kitten edition$', '202767d8-6d76-4407-a441-bd1eccb6fe90'),
            ('90eab301-c021-4b07-b89f-40d2a1a94bf3', '^endeavour$', 'f95aabb1-e779-455a-8b11-e098943e2029'),
            ('5cf1fda7-d371-4ed8-91a4-57b5a4adfd6f', '^f8c lightning civilian$', '319466ad-375a-432c-932e-e6f3ebab7f36'),
            ('50d0a79d-4153-49e6-8c2b-db0c4587aed7', '^f8c lightning executive edition$', 'df11bdab-9b01-4b9d-b1d9-a8caaeb638de'),
            ('18f8840f-d7b9-4478-8159-52f3ee2c49fa', '^gladiator$', 'cb470d14-d682-445f-8a07-31650052d9ca'),
            ('6651358b-e1fc-47a2-b1e8-a481b188fb31', '^(vanduul )?glaive$', 'ca80a412-caf4-47e7-8715-f66ca46e47fd'),
            ('3a46019d-bd67-43bd-a36d-f72e0f160ffb', '^hammerhead 2949 best in show$', 'd7f0fede-dca7-4f7f-b656-a3ce5190dcdf'),
            ('af440c7d-15de-4388-a481-86eaa81c71fe', '^hercules m2 hercules$', '4af1d256-fc6d-4db6-9344-3f5408ad5d7d'),
            ('925a1b26-522b-4c65-af99-b700a742ce91', '^hercules starlifter a2$', '2a00cc23-3c1f-44ed-adc3-2e8d1e80a696'),
            ('276118b6-202f-4acc-a094-b272698ac8a3', '^hercules starlifter c2$', 'ad169070-7783-4247-828d-15d377a7f857'),
            ('1fc21668-5c9c-46b5-aa74-b0b4dfee7c7b', '^hercules starlifter m2$', '4af1d256-fc6d-4db6-9344-3f5408ad5d7d'),
            ('3c482718-7ec9-4377-8c29-0baab155499b', '^hornet f7c$', '52bdc355-f5b5-409d-8245-0a76eeef13c7'),
            ('e22804b7-b9b9-4a33-a717-8e5b1efe6b6d', '^hornet f7c-m heartseeker$', '232ab5fb-d1d7-4955-9fb1-75ecd977c6c9'),
            ('15c217df-430b-4916-a80d-e2ddede8a49a', '^idris-m frigate$', '81f55f1f-2577-4cb5-957c-ea11369f4644'),
            ('3fa75859-9df1-43ec-98f7-ae7ecfe0c972', '^idris-p frigate$', 'c236060d-37ea-4cca-9c76-d335d05612a0'),
            ('405d0454-dcd2-4e6b-a8a6-30d7b608c47f', '^nautilus solstice edition$', '564b3b79-127f-4ce2-b450-04e30a2516c9'),
            ('53db67d0-fc10-4a12-af9a-3cb27c5eb87f', '^nova tank$', '48f078de-3b1f-4da3-af9d-2281c82a6a28'),
            ('e7d3909f-7ae8-4824-bea1-ecaffb0f45a3', '^nox kue$', '8c248add-ffae-4ae7-8a40-085da7e7849e'),
            ('68e821d3-1aea-4c01-9cec-d155c989cede', '^p52( merlin)?$', 'c5aa062e-7a49-4a94-8867-926d3fbfed31'),
            ('d974c5d0-276a-4063-a5ca-c47e337612e8', '^p-72 archimedes emerald$', 'ff5b84b2-1a08-4c51-854e-180bd1ebc0cd'),
            ('b7fb86c7-c903-44a7-a01f-8e41a0215598', '^pirate gladius$', '694c1aa6-b8b0-40d6-a97a-7f59a4fea73e'),
            ('dbf9638c-0c50-4010-8c24-aa7d0deca453', '^pisces$', '549c6ac4-22fe-4d0c-9923-70f3ffa36df5'),
            ('532bc959-aece-44e0-afd7-0cc9ea7b5e72', '^pisces - expedition$', '5f7fbdae-a444-42c6-aec7-69c5c5d8eb97'),
            ('ff307130-7403-4840-b71e-5f2bd7e1ee9c', '^ptv$', '00781e7d-8919-4240-bd00-ca09e7346944'),
            ('593dd539-1e37-410c-9d00-a5927776e5b7', '^reclaimer 2949 best in show$', '3960cfce-409e-4faf-9c03-34ae937aa16d'),
            ('d455f615-c67b-4d1b-9638-01027fc9b071', '^reliant kore - mini hauler$', '89fac43c-33c1-4c0a-9f66-bf5ad81e8862'),
            ('1170a5d1-4645-447e-8c22-f07195705e56', '^reliant mako - news van$', '2d1eb07a-5983-43ce-a2bb-b9105750af35'),
            ('1f462f90-4af1-4f2b-83be-37ec740414d7', '^reliant sen - researcher$', 'f5e414f1-459e-49e5-ac1f-a2ddfd4a54e3'),
            ('68551a87-acd0-470f-a168-01796b530e4e', '^reliant tana - skirmisher$', 'ca0f7a39-75d7-4a2a-9694-f8f94a611c74'),
            ('dc765688-0329-47e9-8087-ceeb77473c93', '^ursa rover$', '5692d6aa-195f-4c34-b027-55e09cfcdfeb'),
            ('dbcd9a8c-e8cc-4543-9c6e-fd82c18ddedd', '^ursa rover fortuna$', '3535bbde-8a5b-40c2-9adb-7b22a3a7a1bf'),
            ('4edc0e33-b53a-45ee-9c5a-233aa1b2a775', '^valkyrie liberator edition$', '3d490eb3-718a-4706-bd1a-337704ae01ba'),
            ('5845d553-d582-43da-ad39-7083046cade1', '^x1 - force$', '19ea6bb8-dcc2-4a4c-b972-59232d6ab39a'),
            ('23e0f06f-7738-4e33-846d-e118b3a1bef4', '^x1 - velocity$', 'a3a871d4-e7a6-48de-84b2-b25f1e2165ad')
        ");

        $this->addSql('DROP TABLE ship_chassis');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE TABLE ship_chassis (id CHAR(36) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci` COMMENT \'(DC2Type:uuid)\', rsi_id INT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, UNIQUE INDEX UNIQ_3BE443B2967433DD (rsi_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');

        $this->addSql('ALTER TABLE ship_name ADD my_hangar_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, ADD ship_matrix_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE INDEX my_hangar_name_idx ON ship_name (my_hangar_name)');
        $this->addSql('CREATE INDEX ship_matrix_name_idx ON ship_name (ship_matrix_name)');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
