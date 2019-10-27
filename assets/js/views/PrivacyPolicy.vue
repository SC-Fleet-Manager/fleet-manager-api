<template>
    <div class="container p-3">
        <nav>
            <ul class="nav mb-3">
                <li class="nav-item mr-3">
                    <a class="nav-link btn btn-primary" href="/">Home</a>
                </li>
                <li class="nav-item">
                    <button type="button" class="nav-link btn btn-secondary" @click="$router.back()">Back</button>
                </li>
            </ul>
        </nav>
        <section class="text-justify">
            <h1>Privacy Policy</h1>

            <p>Protecting your private information is our priority. This Statement of Privacy applies to fleet-manager.space. The Fleet Manager website is a Star Citizen consumer tools and web apps site. By using Fleet Manager website, you consent to the data practices described in this statement.</p>

            <h2>Collection of your Personal Information</h2>

            <p>In order to better provide you with products and services offered on our Site, Fleet Manager may collect personally identifiable information, such as your:</p>
            <ul>
                <li>RSI handle</li>
                <li>RSI number</li>
                <li>Discord handle</li>
                <li>Email</li>
                <li>Star Citizen Fleet informations</li>
            </ul>

            <p>Please keep in mind that if you directly disclose personally identifiable information or personally sensitive data through Fleet Manager public pages, this information may be collected and used by others.</p>

            <p>We do not collect any personal information about you unless you voluntarily provide it to us.</p>

            <h2>Use of your Personal Information</h2>

            <p>Fleet Manager uses your provided personal information to operate its website(s) and deliver the services you have requested.</p>

            <h2>Sharing Information with Third Parties</h2>

            <p>Fleet Manager does not sell, rent or lease its user list to third parties.</p>

            <h2>Automatically Collected Information</h2>

            <p>Information about your computer hardware and software may be automatically collected by Fleet Manager. This information can include: your IP address, browser type, domain names, access times and referring website addresses. This information is used for the operation of the service, to maintain quality of the service, and to provide general statistics regarding use of Fleet Manager website.</p>

            <h2>Use of Cookies</h2>

            <p>Fleet Manager website may use "cookies" to help you personalize your online experience. A cookie is a text file that is placed on your hard disk by a web page server. Cookies cannot be used to run programs or deliver viruses to your computer. Cookies are uniquely assigned to you, and can only be read by a web server in the domain that issued the cookie to you.</p>

            <p>You have the ability to accept or decline cookies. Most Web browsers automatically accept cookies, but you can usually modify your browser setting to decline cookies if you prefer. If you choose to decline cookies, you may not be able to fully experience the interactive features of Fleet Manager services or websites you visit.</p>

            <h2>Links</h2>

            <p>This website contains links to other sites. Please be aware that we are not responsible for the content or privacy practices of such other sites. We encourage our users to be aware when they leave our site and to read the privacy statements of any other site that collects personally identifiable information.</p>

            <h2>Security of your Personal Information</h2>

            <p>Fleet Manager secures your personal information from unauthorized access, use, or disclosure. Fleet Manager uses the following method(s) for this purpose:</p>

            <ul>
                <li>TLS Protocol for HTTPS requests</li>
                <li>Argon2 for password hashes</li>
            </ul>

            <p>We strive to take appropriate security measures to protect against unauthorized access to or alteration of your personal information. Unfortunately, no data transmission over the Internet or any wireless network can be guaranteed to be 100% secure. As a result, while we strive to protect your personal information, you acknowledge that: (a) there are security and privacy limitations inherent to the Internet which are beyond our control; and (b) security, integrity, and privacy of any and all information and data exchanged between you and us through this Site cannot be guaranteed.</p>

            <h2>Children Under Thirteen</h2>

            <p>Fleet Manager does not knowingly collect personally identifiable information from children under the age of thirteen. If you are under the age of thirteen, you must ask your parent or guardian for permission to use this website.</p>

            <h2>Disconnecting your Fleet Manager Account from Third Party Websites</h2>

            <p>You will be able to connect your Fleet Manager account to third party accounts. BY CONNECTING YOUR FLEET MANAGER ACCOUNT TO YOUR THIRD PARTY ACCOUNT, YOU ACKNOWLEDGE AND AGREE THAT YOU ARE CONSENTING TO THE CONTINUOUS RELEASE OF INFORMATION ABOUT YOU TO OTHERS (IN ACCORDANCE WITH YOUR PRIVACY SETTINGS ON THOSE THIRD PARTY SITES). IF YOU DO NOT WANT INFORMATION ABOUT YOU, INCLUDING PERSONALLY IDENTIFYING INFORMATION, TO BE SHARED INTHIS MANNER, DO NOT USE THE THIS FEATURE. You may disconnect your account from a third party account at any time. Users may disconnect their social logins from the account management page by clicking on the 'Logout' link in the top right corner of the page.</p>

            <h2>Changes to this Statement</h2>

            <p>Fleet Manager reserves the right to change this Privacy Policy from time to time. We will notify you about significant changes in the way we treat personal information by sending a notice on Fleet Manager's social networks (e.g., Discord, Reddit...), by placing a prominent notice on our site, and/or by updating any privacy information on this page. Your continued use of the Site and/or Services available through this Site after such modifications will constitute your: (a) acknowledgment of the modified Privacy Policy; and (b) agreement to abide and be bound by that Policy.</p>

            <h2>Contact Information</h2>

            <p>Fleet Manager welcomes your questions or comments regarding this Statement of Privacy. If you believe that Fleet Manager has not adhered to this Statement, please contact Fleet Manager at <b><span class="reverse">moc.liamnotorp [ta] reganam-teelf</span></b>.</p>

            <p>Effective as of May 30, 2019</p>
        </section>
        <nav>
            <ul class="nav mb-3">
                <li class="nav-item mr-3">
                    <a class="nav-link btn btn-primary" href="/">Home</a>
                </li>
                <li class="nav-item">
                    <button type="button" class="nav-link btn btn-secondary" @click="$router.back()">Back</button>
                </li>
            </ul>
        </nav>
    </div>
</template>

<script>
    export default {
        name: 'PrivacyPolicy'
    }
</script>

<style scoped>
    .reverse {
        unicode-bidi: bidi-override;
        direction: rtl;
    }
</style>
