<template>
    <div id="pg">
        <header id="pg-hdr">
            <div class="logo">
                <a href="/"><img src="../img/logo_fm_white.svg" alt="Logo Fleet Manager"></a>
            </div>
            <nav>
                <div class="links">
                    <a target="_blank" href="https://blog.fleet-manager.space" aria-label="Blog"><i class="fas fa-blog"></i></a>
                    <a target="_blank" href="https://github.com/Ioni14/starcitizen-fleet-manager" aria-label="Github"><i class="fab fa-github"></i></a>
                    <a target="_blank" href="https://discord.gg/5EyFpVP" aria-label="Discord"><i class="fab fa-discord"></i></a>
                </div>
                <button v-if="this.user === null" v-once class="btn" type="button"><i class="fas fa-sign-in-alt"></i> Login</button>
                <a v-else v-once class="btn" href="/profile"><i class="fas fa-space-shuttle"></i> Dashboard</a>
            </nav>
        </header>
        <main id="pg-main">
            <section class="header">
                <img @load="onLoad('sprite-character-1')" id="sprite-character-1" src="../img/frontpage/illustrations/character-1.png" alt="">
                <div class="gradient-down"></div>
                <div class="container">
                    <h1>Fleet Manager</h1>
                    <h3>For Star Citizen</h3>
                    <p>Best tool in the verse to manage and share your Organization's and personal fleet</p>
                    <button v-if="this.user === null" v-once class="btn" type="button">Use Now</button>
                    <a v-else v-once class="btn" href="/profile">Use Now</a>
                    <span class="learn-more" @click="smoothScroll('#join-citizens')">learn more <i class="fas fa-angle-down"></i></span>
                </div>
            </section>
            <section class="main">
                <div class="gradient-up"></div>
                <img @load="onLoad('join-citizens')" id="sprite-ship-right-1" src="../img/frontpage/illustrations/vaisseau-1.png" alt="">
                <div class="container" id="join-citizens">
                    <h2>Join thousands of citizens!</h2>
                    <div class="row-stats">
                        <div class="col">
                            <h4>
                                <animated-number v-if="canCountStatistics" :value="countOrga" :formatValue="formatSatistics" :duration="1000"/>
                                <span v-else>0</span>
                            </h4>
                            <p>Organization Fleets</p>
                        </div>
                        <div class="col">
                            <h4>
                                <animated-number v-if="canCountStatistics" :value="countUsers" :formatValue="formatSatistics" :duration="1000"/>
                                <span v-else>0</span>
                            </h4>
                            <p>Citizens Registered</p>
                        </div>
                        <div class="col">
                            <h4>
                                <animated-number v-if="canCountStatistics" :value="countShips" :formatValue="formatSatistics" :duration="1000"/>
                                <span v-else>0</span>
                            </h4>
                            <p>Ships Managed</p>
                        </div>
                    </div>
                </div>
                <section class="screens">
                    <div class="container" id="manager-your-orga-fleet">
                        <div class="row">
                            <div class="col-2">
                                <div class="screens-aside-media">
                                    <svg v-show="!this.screenshots['sprite-first-screenshot']" viewBox="0 0 750 422"><rect width="750" height="422" style="fill:#031217"></rect></svg>
                                    <img v-show="this.screenshots['sprite-first-screenshot']" @load="onLoad('sprite-first-screenshot', true)" src="../img/frontpage/illustrations/screen-1.jpg" alt="">
                                </div>
                            </div>
                            <div class="col-2">
                                <h2 class="screens-title">Manage your Organization's fleet</h2>
                                <div class="screens-aside-media">
                                    <img src="../img/frontpage/illustrations/screen-1.jpg" alt="">
                                </div>
                                <ul class="list-ships">
                                    <li>
                                        <span class="screens-ship">1</span>
                                        <p>Review your Organization's fleet at a glance with easy access to who owns what</p>
                                    </li>
                                    <li>
                                        <span class="screens-ship">2</span>
                                        <p>Many filters to quickly find what you are looking for</p>
                                    </li>
                                    <li>
                                        <span class="screens-ship">3</span>
                                        <p>Share your Organization’s page with the world or hide it event to your citizens</p>
                                    </li>
                                    <li>
                                        <span class="screens-ship">4</span>
                                        <p>Export your fleet for other tools like Fleet Viewer</p>
                                    </li>
                                    <li>
                                        <span class="screens-ship">5</span>
                                        <p>Get in depth statistics about your organization. Track changes and informations about your citizens</p>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="container" id="review-personal-fleet">
                        <div class="row">
                            <div class="col-2">
                                <div class="screens-aside-media">
                                    <svg v-show="!this.screenshots['review-personal-fleet']" viewBox="0 0 750 422"><rect width="750" height="422" style="fill:#031217"></rect></svg>
                                    <img v-show="this.screenshots['review-personal-fleet']" @load="onLoad('review-personal-fleet', true)" src="../img/frontpage/illustrations/screen-2.jpg" alt="">
                                    <img @load="onLoad('review-personal-fleet')" id="sprite-ship-right-top-1" src="../img/frontpage/illustrations/vaisseau-2.png" alt="">
                                </div>
                            </div>
                            <div class="col-2">
                                <h2 class="screens-title">Review your personal fleet</h2>
                                <div class="screens-aside-media">
                                    <svg v-show="!this.screenshots['review-personal-fleet']" viewBox="0 0 750 422"><rect width="750" height="422" style="fill:#031217"></rect></svg>
                                    <img v-show="this.screenshots['review-personal-fleet']" @load="onLoad('review-personal-fleet', true)" src="../img/frontpage/illustrations/screen-2.jpg" alt="">
                                    <img @load="onLoad('review-personal-fleet')" id="sprite-ship-right-top-1-reduced" src="../img/frontpage/illustrations/vaisseau-2.png" alt="">
                                </div>
                                <ul class="list-ships">
                                    <li>
                                        <span class="screens-ship">1</span>
                                        <p>Get a better overview of your fleet than in “My Hangar”</p>
                                    </li>
                                    <li>
                                        <span class="screens-ship">2</span>
                                        <p>Control who can see your fleet. The world, your orga or no one (even your orgas)</p>
                                    </li>
                                    <li>
                                        <span class="screens-ship">3</span>
                                        <p>Upload your fleet Automatically with our dedicated browser extension or Manually</p>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="container" id="official-extension">
                        <div class="row">
                            <div class="col-2">
                                <div class="screens-aside-media">
                                    <svg v-show="!this.screenshots['official-extension']" viewBox="0 0 750 422"><rect width="750" height="422" style="fill:#031217"></rect></svg>
                                    <img v-show="this.screenshots['official-extension']" @load="onLoad('official-extension', true)" src="../img/frontpage/illustrations/screen-3.jpg" alt="">
                                    <img @load="onLoad('official-extension')" id="sprite-ship-left-bottom-1" src="../img/frontpage/illustrations/vaisseau-3.png" alt="">
                                </div>
                            </div>
                            <div class="col-2">
                                <h2 class="screens-title">Official Browser Extension</h2>
                                <div class="screens-aside-media">
                                    <svg v-show="!this.screenshots['official-extension']" viewBox="0 0 750 422"><rect width="750" height="422" style="fill:#031217"></rect></svg>
                                    <img v-show="this.screenshots['official-extension']" @load="onLoad('official-extension', true)" src="../img/frontpage/illustrations/screen-3.jpg" alt="">
                                    <img @load="onLoad('official-extension')" id="sprite-ship-left-bottom-1-reduced" src="../img/frontpage/illustrations/vaisseau-3.png" alt="">
                                </div>
                                <ul class="list-ships">
                                    <li>
                                        <span class="screens-ship">1</span>
                                        <p>Update your fleet with a simple click and forget about updating your checklist or xml shared files</p>
                                    </li>
                                    <li>
                                        <span class="screens-ship">2</span>
                                        <p>Export your fleet as JSON and use it on popular tools like <a target="_blank" href="https://www.starship42.com/fleetview/">FleetView</a>.</p>
                                    <li>
                                        <span class="screens-ship">3</span>
                                        <p>Available on Chrome and Firefox</p>
                                    </li>
                                    <li>
                                        <span class="screens-ship">4</span>
                                        <p>Download extension<br/><a href="https://ext.fleet-manager.space/fleet_manager_extension-latest.xpi" title="Firefox"><i class="fab fa-firefox"></i></a>&nbsp;<a target="_blank" href="https://chrome.google.com/webstore/detail/fleet-manager-extension/hbbadomkekhkhemjjmhkhgiokjhpobhk" title="Chrome"><i class="fab fa-chrome"></i></a></p>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </section>
                <div class="gradient-blue">
                    <div class="container" id="use-it-now">
                        <div class="row">
                            <img @load="onLoad('use-it-now')" id="sprite-useitnow-character-left" src="../img/frontpage/illustrations/character-2.png" alt="">
                            <div class="col">
                                <h2>Use it now</h2>
                                <p>Fleet Manager is an online app to help you keep your organization’s fleet updated and get more insights about it. So you can better prepare your next operations and have fun all together.</p>
                                <button v-if="this.user === null" v-once class="btn" type="button">Start using Fleet Manager</button>
                                <a v-else v-once class="btn" href="/profile">Start using Fleet Manager</a>
                            </div>
                            <img @load="onLoad('use-it-now')" id="sprite-useitnow-character-right" src="../img/frontpage/illustrations/character-3.png" alt="">
                        </div>
                    </div>
                </div>
                <div class="container" id="meet-the-team">
                    <h2>Meet the Team</h2>
                    <div class="row">
                        <div class="col">
                            <img src="../img/frontpage/illustrations/ioni.jpg" alt="photo">
                            <div class="meta">
                                <h4 class="name">
                                    Thomas Talbot
                                </h4>
                                <div class="description">
                                    Lead Developer - Solution Architect
                                </div>
                                <div class="links">
                                    <a target="_blank" href="https://github.com/ioni14"><i class="fab fa-github"></i></a>
                                    <a target="_blank" href="https://www.linkedin.com/in/thomas-talbot/"><i class="fab fa-linkedin"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <img src="../img/frontpage/illustrations/synthese.jpg" alt="photo">
                            <div class="meta">
                                <h4 class="name">
                                    Valentin Ronteix
                                </h4>
                                <div class="description">
                                    Community Manager - Project Manager
                                </div>
                                <div class="links">
                                    <a target="_blank" href="https://github.com/vyrtualsynthese"><i class="fab fa-github"></i></a>
                                    <a target="_blank" href="https://twitch.tv/ashuvidz/"><i class="fab fa-twitch"></i></a>
                                    <a target="_blank" href="https://youtube.com/ashuvidz/"><i class="fab fa-youtube"></i></a>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <img src="../img/frontpage/illustrations/arnaud.jpg" alt="photo">
                            <div class="meta">
                                <h4 class="name">
                                    Arnaud Cantaloube
                                </h4>
                                <div class="description">
                                    Designer
                                </div>
                                <div class="links">
                                    <a target="_blank" href="#"><i class="fab fa-twitter"></i></a>
                                    <a target="_blank" href="#"><i class="fab fa-linkedin"></i></a>
                                    <a target="_blank" href="#"><i class="fa fa-globe"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="gradient-blue">
                    <div class="container" id="supporters">
                        <div class="row">
                            <div class="col">
                                <h2>They supported us</h2>
                                <p>
                                    These Citizens are our top backers, they helped us cover the cost of hosting and maintenance while being active and invested members of the community.
                                </p>
                            </div>
                        </div>
                        <div class="row" id="supporters-backers">
                            <div class="col" v-for="supporter in topSupporters" :key="supporter.name">
                                <img :src="supporter.avatarUrl" alt="avatar">
                                <div class="supporter-description">
                                    <div class="supporter-name">{{ supporter.name }}</div>
                                    <p>{{ supporter.mainOrgaName }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>

        <footer id="pg-ftr">
            <section class="footer">
                <div class="logo">
                    <img src="../img/logo_fm_white.svg" alt="Logo Fleet Manager">
                </div>
                <nav>
                    <div class="policy"><a href="/privacy-policy">Privacy Policy</a><!-- - <a href="#">FAQ</a>--></div>
                    <div class="links">
                        <a target="_blank" href="https://blog.fleet-manager.space"><i class="fas fa-blog"></i></a>
                        <a target="_blank" href="https://github.com/Ioni14/starcitizen-fleet-manager"><i class="fab fa-github"></i></a>
                        <a target="_blank" href="https://discord.gg/5EyFpVP"><i class="fab fa-discord"></i></a>
                    </div>
                </nav>
            </section>
            <section class="copyright">
                <p>Fleet Manager © 2018 - 2020</p>
                <p>All game content and materials are copyright of Cloud Imperium Rights LLC and Cloud Imperium Rights Ltd. Star Citizen®, Roberts Space Industries®, and Cloud Imperium® are registered trademarks of Cloud Imperium Rights LLC. All rights reserved.</p>
            </section>
        </footer>

<!--        <RegistrationAndLoginModal discord-login-url="/connect/discord"></RegistrationAndLoginModal>-->
    </div>
</template>

<script>
import anime from 'animejs/lib/anime.es.js';
import axios from 'axios';
import AnimatedNumber from 'animated-number-vue';
// import RegistrationAndLoginModal from "./views/RegistrationAndLoginModal";

export default {
    name: 'Home',
    components: {AnimatedNumber/*, RegistrationAndLoginModal*/},
    data() {
        return {
            topSupporters: [],
            countOrga: 0,
            countUsers: 0,
            countShips: 0,
            canCountStatistics: false,
            user: null,
            userStated: false,
            animateEls: {
                'sprite-character-1': false,
                'join-citizens': false,
                'review-personal-fleet': false,
                'official-extension': false,
                'use-it-now': false,
                'list-ships-0': false,
                'list-ships-1': false,
                'list-ships-2': false,
                'supporters-backers': false,
            },
            loaded: {
                'sprite-character-1': 0,
                'join-citizens': 0,
                'review-personal-fleet': 0,
                'official-extension': 0,
                'use-it-now': 0,
            },
            screenshots: {
                'sprite-first-screenshot': false,
                'review-personal-fleet': false,
                'official-extension': false,
            }
        };
    },
    created() {
        axios.get('/api/numbers').then(response => {
            this.countOrga = response.data.organizations;
            this.countUsers = response.data.users;
            this.countShips = response.data.ships;
        });
        axios.get('/api/funding/ladder-alltime').then(response => {
            this.topSupporters = response.data.topFundings.slice(0, 12);
            this.$nextTick(() => {
                this.onScroll();
            });
        });
        axios.get('/api/me').then(response => {
            this.user = response.data;
        }).catch(response => {
        }).then(_ => {
            this.userStated = true;
        });
        window.addEventListener('scroll', this.onScroll);
    },
    mounted() {
        this.onScroll();
    },
    methods: {
        formatSatistics(value) {
            return Math.round(value);
        },
        onLoad(elId, isScreenshot) {
            ++this.loaded[elId];
            if (isScreenshot) {
                this.screenshots[elId] = true;
            }
            this.onScroll();
        },
        getPosition(el, withoutScroll) {
            let xPos = 0;
            let yPos = 0;

            while (el) {
                if (el.tagName == "BODY") {
                    let xScroll = 0;
                    let yScroll = 0;
                    if (!withoutScroll) {
                        // deal with browser quirks with body/window/document and page scroll
                        xScroll = el.scrollLeft || document.documentElement.scrollLeft;
                        yScroll = el.scrollTop || document.documentElement.scrollTop;
                    }

                    xPos += (el.offsetLeft - xScroll + el.clientLeft);
                    yPos += (el.offsetTop - yScroll + el.clientTop);
                } else {
                    // for all other non-BODY elements
                    xPos += (el.offsetLeft - el.scrollLeft + el.clientLeft);
                    yPos += (el.offsetTop - el.scrollTop + el.clientTop);
                }
                el = el.offsetParent;
            }

            return {
                x: xPos,
                y: yPos
            };
        },
        onScroll() {
            const thresholdFromTop = window.screen.height * 3/4;
            const thresholdFromBottom = window.screen.height * 1/4;

            if (!this.animateEls['sprite-character-1'] && this.loaded['sprite-character-1'] === 1) {
                const el = document.getElementById('sprite-character-1');
                const pos = this.getPosition(el);
                const elTopScreenY = pos.y;
                const elBottomScreenY = pos.y + el.clientHeight;
                if (elBottomScreenY >= thresholdFromBottom && elTopScreenY <= thresholdFromTop) {
                    this.animateEls['sprite-character-1'] = true;
                    setTimeout(() => { // delay a bit because it's the "first" sprite. otherwise may be too fast for "new" users.
                        anime({
                            targets: '#sprite-character-1',
                            left: '18%',
                            opacity: 1,
                            duration: 1500,
                            easing: 'easeOutQuart', // https://matthewlein.com/tools/ceaser
                        });
                    }, 250);
                }
            }
            if (!this.animateEls['join-citizens'] && this.loaded['join-citizens'] === 1) {
                const el = document.getElementById('join-citizens');
                const pos = this.getPosition(el);
                const elTopScreenY = pos.y;
                const elBottomScreenY = pos.y + el.clientHeight;
                if (elBottomScreenY >= thresholdFromBottom && elTopScreenY <= thresholdFromTop) {
                    this.animateEls['join-citizens'] = true;
                    this.canCountStatistics = true;
                    anime({
                        targets: '#sprite-ship-right-1',
                        right: '19%',
                        opacity: 1,
                        duration: 1500,
                        easing: 'easeOutQuart',
                    });
                }
            }
            if (!this.animateEls['review-personal-fleet'] && this.loaded['review-personal-fleet'] === 4) {
                const el = document.getElementById('review-personal-fleet');
                const pos = this.getPosition(el);
                const elTopScreenY = pos.y;
                const elBottomScreenY = pos.y + el.clientHeight;
                if (elBottomScreenY >= thresholdFromBottom && elTopScreenY <= thresholdFromTop) {
                    this.animateEls['review-personal-fleet'] = true;
                    anime({
                        targets: ['#sprite-ship-right-top-1', '#sprite-ship-right-top-1-reduced'],
                        right: '12%',
                        opacity: 1,
                        duration: 1500,
                        easing: 'easeOutQuart',
                    });
                }
            }
            if (!this.animateEls['official-extension'] && this.loaded['official-extension'] === 4) {
                const el = document.getElementById('official-extension');
                const pos = this.getPosition(el);
                const elTopScreenY = pos.y;
                const elBottomScreenY = pos.y + el.clientHeight;
                if (elBottomScreenY >= thresholdFromBottom && elTopScreenY <= thresholdFromTop) {
                    this.animateEls['official-extension'] = true;
                    anime({
                        targets: ['#sprite-ship-left-bottom-1', '#sprite-ship-left-bottom-1-reduced'],
                        left: '7%',
                        opacity: 1,
                        duration: 1500,
                        easing: 'easeOutQuart',
                    });
                }
            }
            if (!this.animateEls['use-it-now'] && this.loaded['use-it-now'] === 2) {
                const el = document.getElementById('use-it-now');
                const pos = this.getPosition(el);
                const elTopScreenY = pos.y;
                const elBottomScreenY = pos.y + el.clientHeight;
                if (elBottomScreenY >= thresholdFromBottom && elTopScreenY <= thresholdFromTop) {
                    this.animateEls['use-it-now'] = true;
                    anime({
                        targets: '#sprite-useitnow-character-left',
                        left: '16%',
                        opacity: 1,
                        duration: 1500,
                        easing: 'easeOutQuart',
                    });
                    anime({
                        targets: '#sprite-useitnow-character-right',
                        right: '18%',
                        opacity: 1,
                        duration: 1500,
                        easing: 'easeOutQuart',
                    });
                }
            }

            document.querySelectorAll('.list-ships').forEach((el, i) => {
                if (!this.animateEls['list-ships-'+i]) {
                    const pos = this.getPosition(el);
                    const elTopScreenY = pos.y;
                    const elBottomScreenY = pos.y + el.clientHeight;
                    if (elBottomScreenY >= thresholdFromBottom && elTopScreenY <= thresholdFromTop) {
                        this.animateEls['list-ships-'+i] = true;
                        el.querySelectorAll('li').forEach((el) => {
                            el.style.transform = 'translateY(50px)';
                        });
                        anime({
                            targets: el.querySelectorAll('li'),
                            opacity: 1,
                            translateY: '-=50',
                            easing: 'easeOutQuart',
                            delay: function(el, i) {
                                return i * 150;
                            },
                        });
                    }
                }
            });

            if (!this.animateEls['supporters-backers'] && this.topSupporters.length > 0) {
                const el = document.getElementById('supporters-backers');
                const pos = this.getPosition(el);
                const elTopScreenY = pos.y;
                const elBottomScreenY = pos.y + el.clientHeight;
                if (elBottomScreenY >= thresholdFromBottom && elTopScreenY <= thresholdFromTop) {
                    this.animateEls['supporters-backers'] = true;
                    document.querySelectorAll('#supporters-backers .col').forEach((el) => {
                        el.style.transform = 'translateY(50px)';
                    });
                    anime({
                        targets: '#supporters-backers .col',
                        opacity: 1,
                        translateY: '-=50',
                        easing: 'easeInOutQuad',
                        delay: function(el, i) {
                            return i * 75;
                        },
                    });
                }
            }
        },
        smoothScroll(cssSelector) {
            const el = document.querySelector(cssSelector);
            anime({
                targets: 'html, body',
                scrollTop: this.getPosition(el, true).y,
                duration: 1500,
                easing: 'easeOutQuart',
            });
        },
        computePercent(topY, bottomY, windowTopY, windowBottomY) {
            let percent = 0;
            if (topY - windowBottomY > 0) {
                percent = 1;
            } else if (bottomY - windowTopY < 0) {
                percent = 0;
            } else {
                const midY = (topY + bottomY) / 2;
                percent = (midY - windowTopY) / (windowBottomY - windowTopY);
            }
            return percent;
        },
    }
}
</script>

<style lang="scss">
    $fa-font-path: '~@fortawesome/fontawesome-free/webfonts/';
    @import '~@fortawesome/fontawesome-free/scss/fontawesome';
    @import '~@fortawesome/fontawesome-free/scss/solid';
    @import '~@fortawesome/fontawesome-free/scss/brands';
    @import '../css/frontpage/screen.scss';
</style>
