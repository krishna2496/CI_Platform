<template>
	<div class="error-page inner-pages">
		<header>
			<!-- <TopHeader></TopHeader> -->
		</header>
		<main>
			<b-container>
				<div class="error-page-wrap">
					<div class="error-img" :style="{backgroundImage: 'url('+bgImg+')'}"></div>
					<div class="error-content">
						<!-- <h2>{{languageData.label.page_not_found}}</h2> -->
						<h1>
							<b>{{languageData.label.oops}}</b>{{languageData.label.page_not_found_message_heading}}
						</h1>
						<p>{{languageData.label.page_not_found_message_text}}</p>
						<div class="btn-row">
							<b-link class="btn btn-bordersecondary icon-btn"
									:title="languageData.label.go_to_home_page" to="/home">
								<span>{{languageData.label.go_to_home_page}}</span>
								<i>
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 16" width="19" height="15">
										<g id="Main Content">
											<g id="1">
												<g id="Button">
													<path id="Forma 1 copy 12" class="shp0"
														  d="M16.49,1.22c-0.31,-0.3 -0.83,-0.3 -1.16,0c-0.31,0.29 -0.31,0.77 0,1.06l5.88,5.44h-19.39c-0.45,0 -0.81,0.33 -0.81,0.75c0,0.42 0.36,0.76 0.81,0.76h19.39l-5.88,5.43c-0.31,0.3 -0.31,0.78 0,1.07c0.32,0.3 0.85,0.3 1.16,0l7.27,-6.73c0.32,-0.29 0.32,-0.77 0,-1.06z" />
												</g>
											</g>
										</g>
									</svg>
								</i>
							</b-link>
						</div>
					</div>
				</div>
			</b-container>
		</main>
	</div>
</template>

<script>
	import store from "../store";
	export default {
		components: {},
		name: "404",
		data() {
			return {
				bgImg: store.state.imagePath + '/assets/images/404-img.png',
				languageData: []
			};
		},
		created() {
			this.languageData = JSON.parse(store.state.languageLabel);
		},
		methods: {}
	};
</script>
