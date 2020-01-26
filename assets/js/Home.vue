<template>
    <div id="pg">
        <header id="pg-hdr">
            <div class="logo">
                <a href="/"><img src="../img/logo_fm_white.svg" alt="Logo Fleet Manager"></a>
            </div>
            <nav>
                <div class="links">
                    <a href="#" aria-label="Blog"><i class="fas fa-blog"></i></a>
                    <a href="#" aria-label="Github"><i class="fab fa-github"></i></a>
                    <a href="#" aria-label="Discord"><i class="fab fa-discord"></i></a>
                </div>
                <a href="#" class="btn"><i class="fas fa-sign-in-alt"></i> Login</a>
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
                    <a href="#" class="btn">Use Now</a>
                    <a href="#more" class="anchor">learn more <i class="fas fa-angle-down"></i></a>
                </div>
            </section>
            <section class="main">
                <div class="gradient-up"></div>
                <img @load="onLoad('join-citizens')" id="sprite-ship-right-1" src="../img/frontpage/illustrations/vaisseau-1.png" alt="">
                <div id="more"></div>
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
                    <div class="container">
                        <div class="row">
                            <div class="col-2">
                                <div class="screens-aside-media">
                                    <svg v-show="!this.screenshots['sprite-ship-right-top-1']" viewBox="0 0 750 422"><rect width="750" height="422" style="fill:#031217"></rect></svg>
                                    <img v-show="this.screenshots['sprite-ship-right-top-1']" @load="onLoad('sprite-ship-right-top-1', true)" src="../img/frontpage/illustrations/screen-2.jpg" alt="">
                                    <img @load="onLoad('sprite-ship-right-top-1')" id="sprite-ship-right-top-1" src="../img/frontpage/illustrations/vaisseau-2.png" alt="">
                                </div>
                            </div>
                            <div class="col-2">
                                <h2 class="screens-title">Review your personal fleet</h2>
                                <div class="screens-aside-media">
                                    <img src="../img/frontpage/illustrations/screen-2.jpg" alt="">
                                    <img id="sprite-ship-right-top-1-reduced" src="../img/frontpage/illustrations/vaisseau-2.png" alt="">
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
                    <div class="container">
                        <div class="row">
                            <div class="col-2">
                                <div class="screens-aside-media">
                                    <svg v-show="!this.screenshots['sprite-ship-left-bottom-1']" viewBox="0 0 750 422"><rect width="750" height="422" style="fill:#031217"></rect></svg>
                                    <img v-show="this.screenshots['sprite-ship-left-bottom-1']" @load="onLoad('sprite-ship-left-bottom-1', true)" src="../img/frontpage/illustrations/screen-3.jpg" alt="">
                                    <img @load="onLoad('sprite-ship-left-bottom-1')" id="sprite-ship-left-bottom-1" src="../img/frontpage/illustrations/vaisseau-3.png" alt="">
                                </div>
                            </div>
                            <div class="col-2">
                                <h2 class="screens-title">Official Browser Extension</h2>
                                <div class="screens-aside-media">
                                    <img src="../img/frontpage/illustrations/screen-3.jpg" alt="">
                                    <img id="sprite-ship-left-bottom-1-reduced" src="../img/frontpage/illustrations/vaisseau-3.png" alt="">
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
                                        <p>Download extension<br/><a href="#" title="Firefox"><i class="fab fa-firefox"></i></a>&nbsp;<a href="#" title="Chrome"><i class="fab fa-chrome"></i></a></p>
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
                                <a href="#" class="btn">Start using Fleet Manager</a>
                            </div>
                            <img @load="onLoad('use-it-now')" id="sprite-useitnow-character-right" src="../img/frontpage/illustrations/character-3.png" alt="">
                        </div>
                    </div>
                </div>
                <div class="container" id="meet-the-team">
                    <h2>Meet the Team</h2>
                    <div class="row">
                        <div class="col">
                            <img src="../img/frontpage/illustrations/ioni.jpg">
                            <div class="meta">
                                <h4 class="name">
                                    Thomas Talbot
                                </h4>
                                <div class="description">
                                    Lead Developer - Solution Architect
                                </div>
                                <div class="links">
                                    <a href="#">
                                        <i class="fab fa-github"></i>
                                    </a>
                                    <a href="#">
                                        <i class="fab fa-linkedin"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <img src="../img/frontpage/illustrations/synthese.jpg">
                            <div class="meta">
                                <h4 class="name">
                                    Valentin Ronteix
                                </h4>
                                <div class="description">
                                    Community Manager - Project Manager
                                </div>
                                <div class="links">
                                    <a href="#">
                                        <i class="fab fa-github"></i>
                                    </a>
                                    <a href="#">
                                        <i class="fab fa-twitch"></i>
                                    </a>
                                    <a href="#">
                                        <i class="fab fa-youtube"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="col">
                            <img src="../img/frontpage/illustrations/arnaud.jpg">
                            <div class="meta">
                                <h4 class="name">
                                    Arnaud Cantaloube
                                </h4>
                                <div class="description">
                                    Designer
                                </div>
                                <div class="links">
                                    <a href="#">
                                        <i class="fab fa-twitter"></i>
                                    </a>
                                    <a href="#">
                                        <i class="fab fa-linkedin"></i>
                                    </a>
                                    <a href="#">
                                        <i class="fa fa-globe"></i>
                                    </a>
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
                            <div class="col">
                                <div class="img-round">
                                    <img src="../img/frontpage/illustrations/ioni.jpg" alt="avatar">
                                </div>
                                <div class="supporter-content">
                                    <div class="supporter">Name</div>
                                    <p>Org Name</p>
                                </div>
                            </div>
                            <div class="col">
                                <div class="img-round">
                                    <img src="../img/frontpage/illustrations/ioni.jpg" alt="avatar">
                                </div>
                                <div class="supporter-content">
                                    <div class="supporter">Name</div>
                                    <p>Org Name</p>
                                </div>
                            </div>
                            <div class="col">
                                <div class="img-round">
                                    <img src="../img/frontpage/illustrations/ioni.jpg" alt="avatar">
                                </div>
                                <div class="supporter-content">
                                    <div class="supporter">Name</div>
                                    <p>Org Name</p>
                                </div>
                            </div>
                            <div class="col">
                                <div class="img-round">
                                    <img src="../img/frontpage/illustrations/ioni.jpg" alt="avatar">
                                </div>
                                <div class="supporter-content">
                                    <div class="supporter">Name</div>
                                    <p>Org Name</p>
                                </div>
                            </div>
                            <div class="col">
                                <div class="img-round">
                                    <img src="../img/frontpage/illustrations/ioni.jpg" alt="avatar">
                                </div>
                                <div class="supporter-content">
                                    <div class="supporter">Name</div>
                                    <p>Org Name</p>
                                </div>
                            </div>

                            <div class="col">
                                <div class="img-round">
                                    <img src="../img/frontpage/illustrations/ioni.jpg" alt="avatar">
                                </div>
                                <div class="supporter-content">
                                    <div class="supporter">Name</div>
                                    <p>Org Name</p>
                                </div>
                            </div>
                            <div class="col">
                                <div class="img-round">
                                    <img src="../img/frontpage/illustrations/ioni.jpg" alt="avatar">
                                </div>
                                <div class="supporter-content">
                                    <div class="supporter">Name</div>
                                    <p>Org Name</p>
                                </div>
                            </div>
                            <div class="col">
                                <div class="img-round">
                                    <img src="../img/frontpage/illustrations/ioni.jpg" alt="avatar">
                                </div>
                                <div class="supporter-content">
                                    <div class="supporter">Name</div>
                                    <p>Org Name</p>
                                </div>
                            </div>
                            <div class="col">
                                <div class="img-round">
                                    <img src="../img/frontpage/illustrations/ioni.jpg" alt="avatar">
                                </div>
                                <div class="supporter-content">
                                    <div class="supporter">Name</div>
                                    <p>Org Name</p>
                                </div>
                            </div>
                            <div class="col"></div>
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
                    <div class="policy"><a href="#">Privacy Policy</a><!-- - <a href="#">FAQ</a>--></div>
                    <div class="links">
                        <a target="_blank" href="#"><i class="fas fa-blog"></i></a>
                        <a target="_blank" href="#"><i class="fab fa-github"></i></a>
                        <a target="_blank" href="#"><i class="fab fa-discord"></i></a>
                    </div>
                </nav>
            </section>
            <section class="copyright">
                <p>Fleet Manager © 2018 - 2020</p>
                <p>All game content and materials are copyright of Cloud Imperium Rights LLC and Cloud Imperium Rights Ltd. Star Citizen®, Roberts Space Industries®, and Cloud Imperium® are registered trademarks of Cloud Imperium Rights LLC. All rights reserved.</p>
            </section>
        </footer>
    </div>
</template>

<script>
import anime from 'animejs/lib/anime.es.js';
import axios from 'axios';
import AnimatedNumber from 'animated-number-vue';

export default {
    name: 'Home',
    props: [],
    components: {AnimatedNumber},
    data() {
        return {
            countOrga: 0,
            countUsers: 0,
            countShips: 0,
            canCountStatistics: false,
            animateEls: {
                'sprite-character-1': false,
                'join-citizens': false,
                'sprite-ship-right-top-1': false,
                'sprite-ship-left-bottom-1': false,
                'use-it-now': false,
                'list-ships-0': false,
                'list-ships-1': false,
                'list-ships-2': false,
                'supporters-backers': false,
            },
            loaded: {
                'sprite-character-1': 0,
                'join-citizens': 0,
                'sprite-ship-right-top-1': 0,
                'sprite-ship-left-bottom-1': 0,
                'use-it-now': 0,
            },
            screenshots: {
                'sprite-first-screenshot': false,
                'sprite-ship-right-top-1': false,
                'sprite-ship-left-bottom-1': false,
            }
        };
    },
    created() {
        axios.get('/api/numbers').then(response => {
            this.countOrga = response.data.organizations;
            this.countUsers = response.data.users;
            this.countShips = response.data.ships;
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
        getPosition(el) {
            let xPos = 0;
            let yPos = 0;

            while (el) {
                if (el.tagName == "BODY") {
                    // deal with browser quirks with body/window/document and page scroll
                    const xScroll = el.scrollLeft || document.documentElement.scrollLeft;
                    const yScroll = el.scrollTop || document.documentElement.scrollTop;

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
                    anime({
                        targets: '#sprite-character-1',
                        left: '18%',
                        opacity: 1,
                        duration: 1500,
                        easing: 'easeOutQuart', // https://matthewlein.com/tools/ceaser
                    });
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
            if (!this.animateEls['sprite-ship-right-top-1'] && this.loaded['sprite-ship-right-top-1'] === 2) {
                const el = document.getElementById('sprite-ship-right-top-1');
                const pos = this.getPosition(el);
                const elTopScreenY = pos.y;
                const elBottomScreenY = pos.y + el.clientHeight;
                if (elBottomScreenY >= thresholdFromBottom && elTopScreenY <= thresholdFromTop) {
                    this.animateEls['sprite-ship-right-top-1'] = true;
                    anime({
                        targets: ['#sprite-ship-right-top-1', '#sprite-ship-right-top-1-reduced'],
                        right: '12%',
                        opacity: 1,
                        duration: 1500,
                        easing: 'easeOutQuart',
                    });
                }
            }
            if (!this.animateEls['sprite-ship-left-bottom-1'] && this.loaded['sprite-ship-left-bottom-1'] === 2) {
                const el = document.getElementById('sprite-ship-left-bottom-1');
                const pos = this.getPosition(el);
                const elTopScreenY = pos.y;
                const elBottomScreenY = pos.y + el.clientHeight;
                if (elBottomScreenY >= thresholdFromBottom && elTopScreenY <= thresholdFromTop) {
                    this.animateEls['sprite-ship-left-bottom-1'] = true;
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

            if (!this.animateEls['supporters-backers']) {
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
                        easing: 'easeOutQuart',
                        delay: function(el, i) {
                            return i * 100;
                        },
                    });
                }
            }
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
