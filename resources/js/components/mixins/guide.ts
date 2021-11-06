import { Component, Vue } from "vue-property-decorator";
import { Guide, Seniority } from "@/types/models";

@Component
export class GuideMixin extends Vue {
  protected seniorityTooltips = ['Junior', 'Mid-Level', 'Senior'];
  protected readonly guide!: Guide;

  get progressBarValue(): number {
    switch (this.seniorityLabel) {
      case Seniority.junior:
        return 1;

      case Seniority.mid:
        return 2;

      case Seniority.senior:
        return 3;

      default:
        return 1;
    }
  }

  get seniorityLabel(): string {
    return Seniority[this.guide.seniority];
  }
}
