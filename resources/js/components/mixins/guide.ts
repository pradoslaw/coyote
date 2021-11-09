import { Component, Vue } from "vue-property-decorator";
import { Guide, JUNIOR, MID, SENIOR, Seniority } from "@/types/models";

@Component
export class GuideMixin extends Vue {
  protected readonly roles = { [JUNIOR]: Seniority.junior, [MID]: Seniority.mid, [SENIOR]: Seniority.senior };
  protected readonly guide!: Guide;

  get progressBarValue(): number {
    return Object.fromEntries(Object.entries(Object.keys(this.roles)).map(a => a.reverse()))[this.guide.role] + 1;
  }

  get seniorityLabel(): string {
    return this.roles[this.guide.role];
  }
}
