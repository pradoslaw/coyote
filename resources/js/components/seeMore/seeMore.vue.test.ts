import {describe, test} from "@jest/globals";
import {assertContains, assertEquals, assertFalse, assertTrue} from "../../../../survey/test/assert";
import {Component, render} from "../../../../survey/test/render";
import VueSeeMore from "./seeMore.vue";

describe('wrapper', () => {
  test('has "see-more" bem block', async () => {
    assertContains((await wrapped()).classes(), 'see-more');
  });

  test('wrapped element sees "Zobacz całość"', async () => {
    assertEquals((await wrapped()).textBy('.see-more__unwrap'), 'Zobacz całość');
  });

  test('regular element does not have unwrap element', async () => {
    assertFalse((await regular()).exists('.see-more__unwrap'));
  });

  test('regular element does not have wrapped modifier', async () => {
    assertFalse(isWrapped(await regular()));
  });

  test('wrapped element has wrapped modifier', async () => {
    assertTrue(isWrapped(await wrapped()));
  });

  test('sees component slot in content element', async () => {
    const seeMore = render(VueSeeMore, {}, {}, {default: 'foo'});
    assertEquals(seeMore.textBy('.see-more__content'), 'foo');
  });

  test('clicking unwrap element unwraps the component', async () => {
    const seeMore: Component = await wrapped();
    await seeMore.click('.see-more__unwrap a');
    assertFalse(isWrapped(seeMore));
  });

  test('regular element does not have {maxHeight} style', async () => {
    assertEquals((await regular()).attributeOf('.see-more__content', 'style'), 'max-height: none;');
  });

  test('within drop in height, element is not wrapped', async () => {
    const instance = await seeMore(1000, 1149);
    assertFalse(isWrapped(instance));
  });

  test('above the drop in height, element is wrapped to the height', async () => {
    const instance = await seeMore(1000, 1150);
    assertTrue(isWrapped(instance));
    assertEquals(instance.attributeOf('.see-more__content', 'style'), 'max-height: 1000px;');
  });

  async function wrapped(): Promise<Component> {
    return await seeMore(300, 500);
  }

  async function regular(): Promise<Component> {
    return await seeMore(300, 150);
  }

  async function seeMore(height: number | undefined, contentHeight: number): Promise<Component> {
    const instance = render(VueSeeMore, {height}, {}, {}, {
      contentHeight(): number {
        return contentHeight || 350;
      },
    });
    await instance.waitForRefresh();
    return instance;
  }

  function isWrapped(seeMore: Component): boolean {
    return seeMore.exists('.see-more--wrapped');
  }
});
