// @flow

import {Dom, Loc} from 'main.core';
import { mapWritableState } from 'ui.vue3.pinia';

export class Test
{
	#app;
	constructor(rootNode): void
	{
		this.rootNode = document.querySelector(rootNode);
	}

	start(): void
	{
		this.attachTemplate();
	}
	attachTemplate(): void
	{
		this.#app = BX.Vue3.BitrixVue.createApp({
			components: {

			},
			template: `
				<div>
					{{'test'}}
				</div>
				`,
			data: () => ({

			}),
		});
		this.#app.mount(this.rootNode);
	}
}
