<template>
    <div>
        <nav class="navbar navbar-expand-lg" style="background-color: rgba(15, 15, 15, 1)">
            <div class="container">
                <a class="navbar-brand" href="/">
                    <img src="../img/logo.png" height="45" alt="Fleet Manager Logo">
                </a>
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent"
                      aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse text-white">
                    <div class="ml-auto d-flex">
                        <ul class="navbar-nav">
                            <li class="nav-item">
                                <a href="https://github.com/Ioni14/starcitizen-fleet-manager" target="_blank" class="nav-link text-white"><i class="fab fa-github fa-2x"></i></a>
                            </li>
                            <li class="nav-item">
                                <a href="https://discord.gg/5EyFpVP" target="_blank" class="nav-link text-white"><i class="fab fa-discord fa-2x"></i></a>
                            </li>
                            <li class="nav-item ml-2" v-if="userStated" v-once>
                                <button v-if="this.user === null" v-once v-b-modal.modal-login class="nav-link btn btn-primary text-white" type="button"><i class="fas fa-door-open"></i> Login<!-- / Register--></button>
                                <a v-else v-once class="nav-link btn btn-primary text-white" href="/profile"><i class="fas fa-space-shuttle"></i> Dashboard</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>

        <header id="hero-full-screen" ref="heroFullScreen">
            <div class="container d-flex align-items-center text-white" style="min-height: calc(100vh - 71px);">
                <div class="jumbotron" style="background-color: rgba(0, 0, 0, 0.8);">
                    <h1 id="hero-full-screen-title" class="display-2">Fleet Manager</h1>
                    <p id="hero-full-screen-subtitle" class="mb-4 mt-1" style="font-size: 2rem;">Manage your Star Citizen fleets</p>
                    <p class="mb-4" style="font-size: 1.1rem;">Fleet Manager is an online open source tool to help you <strong>manage</strong> your <strong>personal</strong> and <strong>organizations</strong> <strong>Star Citizen Fleets</strong>, share it with your friends and your organizations.</p>
                    <div class="text-center">
                        <button v-if="this.user === null" v-once v-b-modal.modal-login class="btn btn-primary btn-lg font-2xl" type="button">Start using Fleet Manager now</button>
                        <a v-else v-once class="btn btn-primary btn-lg font-2xl" href="/profile">Start using Fleet Manager now</a>
                    </div>
                </div>
            </div>
        </header>

        <section id="features-part" ref="featuresPart">
            <div class="container d-flex align-items-center text-white" style="min-height: 75vh">
                <div class="w-100">
                    <div class="row text-center">
                        <div class="col-12 mb-5">
                            <h2 id="features-part-title" class="display-3 text-uppercase" style="text-shadow: 0 0 5px rgba(10, 10, 10, 1);">Features</h2>
                        </div>
                    </div>
                    <div class="row text-center">
                        <div class="col-md-4 mb-3">
                            <div class="card h-100" style="background-color: rgba(15, 15, 15, 0.9);">
                                <div class="card-body">
                                    <i class="fas fa-plane fa-7x pb-4"></i>
                                    <h3 class="h1">Manage</h3>
                                    <p class="card-text h5">Manage all your organization's fleets the easy way.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card h-100" style="background-color: rgba(15, 15, 15, 0.9);">
                                <div class="card-body">
                                    <i class="fas fa-share-alt fa-7x pb-4"></i>
                                    <h3 class="h1">Share</h3>
                                    <p class="card-text h5">Share your fleet with everyone, orga only or keep it private.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card h-100" style="background-color: rgba(15, 15, 15, 0.9);">
                                <div class="card-body">
                                    <i class="fas fa-sync-alt fa-7x pb-4"></i>
                                    <h3 class="h1">Update</h3>
                                    <p class="card-text h5">Update your fleet in one click with our dedicated <strong>browser extension</strong>.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="statistics" ref="statistics">
            <div class="container text-white">
                <div class="row text-center align-content-center" style="min-height: 25vh">
                    <div class="col-md-4 mb-3">
                        <div class="statistics-nb-value">
                            <animated-number v-if="canCountStatistics" :value="countOrga" :formatValue="formatSatistics" :duration="1000"/>
                            <span v-else>0</span></div>
                        <div class="statistics-nb-label">Organizations' Fleets</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="statistics-nb-value">
                            <animated-number v-if="canCountStatistics" :value="countUsers" :formatValue="formatSatistics" :duration="1000"/>
                            <span v-else>0</span></div>
                        <div class="statistics-nb-label">Citizens Registered</div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <div class="statistics-nb-value">
                            <animated-number v-if="canCountStatistics" :value="countShips" :formatValue="formatSatistics" :duration="1000"/>
                            <span v-else>0</span></div>
                        <div class="statistics-nb-label">Ships Managed</div>
                    </div>
                </div>
            </div>
        </section>

        <section id="who-use" ref="whoUse">
            <div class="container d-flex align-items-center text-white" style="min-height: 75vh;">
                <div class="w-100">
                    <div class="row text-center">
                        <div class="col-12 mb-5">
                            <h2 id="who-use-title" class="display-3 text-uppercase" style="text-shadow: 0 0 5px rgba(10, 10, 10, 1);">Who uses it ?</h2>
                        </div>
                    </div>
                    <div class="row text-center">
                        <div class="col-md-4 mb-3">
                            <div class="card h-100" style="background-color: rgba(15, 15, 15, 0.9);">
                                <img src="../img/org.jpg" class="card-img-top" alt="">
                                <div class="card-body">
                                    <h3 class="h1">Organizations</h3>
                                    <p class="card-text h5">to keep up to date inventory of their fleet.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card h-100" style="background-color: rgba(15, 15, 15, 0.9);">
                                <img src="../img/streamer.jpg" class="card-img-top" alt="">
                                <div class="card-body">
                                    <h3 class="h1">Influencers</h3>
                                    <p class="card-text h5">to share their current fleet with their fans.</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card h-100" style="background-color: rgba(15, 15, 15, 0.9);">
                                <img src="../img/citizen.jpg" class="card-img-top" alt="">
                                <div class="card-body">
                                    <h3 class="h1">Citizens</h3>
                                    <p class="card-text h5">looking for better information about their fleet.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="social-networks">
            <div class="container text-white">
                <div class="row text-center align-content-center" style="min-height: 25vh">
                    <div class="col-4 mb-3">
                        <div class="social-networks-label pb-3">Roadmap</div>
                        <a href="https://trello.com/b/V2daCSis/fleet-manager-road-map" target="_blank"><i class="fab fa-trello fa-8x"></i></a>
                    </div>
                    <div class="col-4 mb-3">
                        <div class="social-networks-label pb-3">Sources</div>
                        <a href="https://github.com/Ioni14/starcitizen-fleet-manager" target="_blank"><i class="fab fa-github fa-8x"></i></a>
                    </div>
                    <div class="col-4 mb-3">
                        <div class="social-networks-label pb-3">Ideas,&nbsp;Bugs</div>
                        <a href="https://discord.gg/sD7Wp3u" target="_blank"><i class="fab fa-discord fa-8x"></i></a>
                    </div>
                </div>
            </div>
        </section>

        <section id="team" ref="team">
            <div class="container d-flex align-items-center text-white" style="min-height: 75vh;">
                <div class="w-100">
                    <div class="row text-center">
                        <div class="col-12 mb-5">
                            <h2 id="team-title" class="display-3 text-uppercase" style="text-shadow: 0 0 5px rgba(10, 10, 10, 1);">Fleet Manager Team</h2>
                        </div>
                    </div>
                    <div class="row text-center">
                        <div class="col-md-6 col-lg-5 offset-lg-1 col-xl-4 offset-xl-1 mb-3">
                            <div class="card h-100" style="background-color: rgba(15, 15, 15, 0.9);">
                                <img src="../img/ioni.jpg" class="card-img-top" alt="">
                                <div class="card-body d-flex flex-column">
                                    <h3 class="h1">Ioni</h3>
                                    <p class="card-text h5 mb-2">Lead&nbsp;Developer - Solution&nbsp;Architect</p>
                                    <div class="row mt-auto">
                                        <div class="col-12">
                                            <a href="https://github.com/ioni14" target="_blank"><i class="fab fa-github fa-3x"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-5 col-xl-4 offset-xl-2 mb-3">
                            <div class="card h-100" style="background-color: rgba(15, 15, 15, 0.9);">
                                <img src="../img/synthese.jpg" class="card-img-top" alt="">
                                <div class="card-body d-flex flex-column">
                                    <h3 class="h1">Vyrtual Synthese</h3>
                                    <p class="card-text h5 mb-2">Community&nbsp;Manager - Project&nbsp;Manager</p>
                                    <div class="row mt-auto">
                                        <div class="col-4">
                                            <a href="https://github.com/vyrtualsynthese" target="_blank"><i class="fab fa-github fa-3x"></i></a>
                                        </div>
                                        <div class="col-4">
                                            <a href="https://twitch.tv/ashuvidz/" target="_blank"><i class="fab fa-twitch fa-3x"></i></a>
                                        </div>
                                        <div class="col-4">
                                            <a href="https://youtube.com/ashuvidz/" target="_blank"><i class="fab fa-youtube fa-3x"></i></a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <footer id="footer">
            <div class="container text-white">
                <div class="row align-items-center">
                    <div class="col-6">
                        <a class="navbar-brand" href="/"><img src="../img/logo.png" height="45" alt="Fleet Manager Logo"></a>
                        <a href="/privacy-policy">Privacy policy</a>
                    </div>
                    <div class="col-6 text-right">
                        <a href="https://github.com/Ioni14/starcitizen-fleet-manager" target="_blank" class="mr-3"><i class="fab fa-github fa-2x"></i></a>
                        <a href="https://discord.gg/5EyFpVP" target="_blank"><i class="fab fa-discord fa-2x"></i></a>
                    </div>
                </div>
            </div>
        </footer>

        <b-modal
            id="modal-login"
            ref="modalLogin"
            title="Connect to Fleet Manager"
            size="md"
            centered hide-footer
            header-bg-variant="dark"
            header-text-variant="light"
            body-bg-variant="dark"
            body-text-variant="light"
            footer-bg-variant="dark"
            footer-text-variant="light"
        >
            <b-row class="justify-content-center">
                <b-col>
                    <b-form class="text-center mt-3 mb-3">
                        <b-button size="lg" style="background-color: #7289da; color: #fff;" class="px-5" :href="discordLoginUrl"><i class="fab fa-discord"></i> Login with Discord</b-button>
                    </b-form>
                </b-col>
            </b-row>
        </b-modal>
    </div>
</template>

<script>
import axios from 'axios';
import AnimatedNumber from 'animated-number-vue';

export default {
    name: 'Login',
    props: ['discordLoginUrl'],
    components: {AnimatedNumber},
    data() {
        return {
            countOrga: 0,
            countUsers: 0,
            countShips: 0,
            canCountStatistics: false,
            user: null,
            userStated: false,
        };
    },
    created() {
        axios.get('/api/numbers').then(response => {
            this.countOrga = response.data.organizations;
            this.countUsers = response.data.users;
            this.countShips = response.data.ships;
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
        onScroll() {
            if (!this.canCountStatistics) {
                const topY = this.$refs.statistics.offsetTop;
                const bottomY = topY + this.$refs.statistics.clientHeight;
                const statsTopScreenY = topY - window.scrollY; // the Y-pos of statistics block with topscreen at origin
                const statsBottomScreenY = bottomY - window.scrollY;
                const thresholdFromTop = window.screen.height * 4/5;
                const thresholdFromBottom = window.screen.height * 1/5;

                if (statsBottomScreenY >= thresholdFromBottom && statsTopScreenY <= thresholdFromTop) {
                    this.canCountStatistics = true;
                }
            }

            if (window.innerWidth >= 1200) { // no parallax on small screens
                const windowTopY = window.scrollY;
                const windowBottomY = windowTopY + window.screen.height;

                // parallax #heroFullScreen
                let topY = this.$refs.heroFullScreen.offsetTop;
                let bottomY = topY + this.$refs.heroFullScreen.clientHeight;
                this.$refs.heroFullScreen.style.backgroundPositionY = this.computePercent(topY, bottomY, windowTopY, windowBottomY) * 100 + '%';

                // parallax #featuresPart
                topY = this.$refs.featuresPart.offsetTop;
                bottomY = topY + this.$refs.featuresPart.clientHeight;
                this.$refs.featuresPart.style.backgroundPositionY = this.computePercent(topY, bottomY, windowTopY, windowBottomY) * 100 + '%';

                // parallax #whoUse
                topY = this.$refs.whoUse.offsetTop;
                bottomY = topY + this.$refs.whoUse.clientHeight;
                this.$refs.whoUse.style.backgroundPositionY = this.computePercent(topY, bottomY, windowTopY, windowBottomY) * 100 + '%';

                // parallax #team
                topY = this.$refs.team.offsetTop;
                bottomY = topY + this.$refs.team.clientHeight;
                this.$refs.team.style.backgroundPositionY = this.computePercent(topY, bottomY, windowTopY, windowBottomY) * 100 + '%';
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
        }
    }
}
</script>

<style lang="scss">
    $fa-font-path: '~@fortawesome/fontawesome-free/webfonts/';
    @import '~@fortawesome/fontawesome-free/scss/fontawesome';
    @import '~@fortawesome/fontawesome-free/scss/solid';
    @import '~@fortawesome/fontawesome-free/scss/brands';
    @import '~bootstrap-vue/dist/bootstrap-vue.css';
    @import '../css/style';

    #hero-full-screen {
        background-image: url('../img/hero.jpg');
        background-size: cover;
        background-position: center 0;
        background-repeat: repeat-y;
        transition: 0s linear;
        transition-property: background-position;

        .jumbotron {
            margin-bottom: 0;
            #hero-full-screen-title {
                @include media-breakpoint-down(sm) {
                    font-size: 4rem;
                }
            }
            #hero-full-screen-subtitle {
                @include media-breakpoint-down(sm) {
                    font-size: 3rem;
                }
            }
        }
    }
    #features-part {
        background: linear-gradient(rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0)), url('../img/jupiter.jpg');
        background-size: cover;
        background-position: center 0;
        background-repeat: repeat-y;
        transition: 0s linear;
        transition-property: background-position;

        @include media-breakpoint-down(sm) {
            padding: 5rem 1rem;
        }
        #features-part-title {
            @include media-breakpoint-down(sm) {
                font-size: 3rem;
            }
        }
    }
    #statistics {
        background-color: rgba(15, 15, 15, 1);
        background-size: cover;
        padding: 3rem 1rem;
        .statistics-nb-value {
            text-shadow: 0 0 10px rgba(220, 220, 220, 1);
            font-size: 6rem;
            @include media-breakpoint-only(md) {
                font-size: 5rem;
            }
            @include media-breakpoint-down(sm) {
                font-size: 4rem;
            }
        }
        .statistics-nb-label {
            font-size: 2rem;
            @include media-breakpoint-only(md) {
                font-size: 1.7rem;
            }
            @include media-breakpoint-down(sm) {
                font-size: 1.5rem;
            }
        }
    }
    #who-use {
        background: linear-gradient(rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0)), url('../img/bg1.jpg');
        background-size: cover;
        background-position: center 0;
        background-repeat: repeat-y;
        transition: 0s linear;
        transition-property: background-position;

        @include media-breakpoint-down(sm) {
            padding: 5rem 1rem;
        }
        #who-use-title {
            @include media-breakpoint-down(sm) {
                font-size: 3rem;
            }
        }
    }
    #social-networks {
        background-color: rgba(15, 15, 15, 1);
        background-size: cover;
        padding: 3rem 1rem;
        .social-networks-label {
            font-size: 2rem;
            @include media-breakpoint-only(md) {
                font-size: 1.7rem;
            }
            @include media-breakpoint-down(sm) {
                font-size: 1.5rem;
            }
            @include media-breakpoint-only(xs) {
                font-size: 1.2rem;
            }
        }
        .fab {
            @include media-breakpoint-only(xs) {
                font-size: 5rem;
            }
        }
        a {
            color: white;
        }
    }
    #team {
        background: linear-gradient(rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0)), url('../img/bg-4.jpg');
        background-size: cover;
        background-position: center 0;
        background-repeat: repeat-y;
        transition: 0s linear;
        transition-property: background-position;
        padding: 5rem 1rem;

        #team-title {
            @include media-breakpoint-down(sm) {
                font-size: 2.7rem;
            }
        }
        a {
            color: white;
        }
    }
    #footer {
        background-color: rgba(15, 15, 15, 1);
        a {
            color: white;
        }
    }

    #modal-login {
        .modal-title {
            font-size: 1.2rem;
        }
    }
</style>
