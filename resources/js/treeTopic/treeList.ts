interface Record<T> {
  id: number;
  parentId: number | null;
  payload: T;
  children: Record<T>[];
  level: number;
  isLastChild: boolean | null;
  ignoreChildren: boolean;
}

export class TreeList<T> {
  private roots: Record<T>[] = [];
  private records: Map<number, Record<T>> = new Map<number, Record<T>>();

  constructor(private sorter: (a: T, b: T) => number) {
  }

  add(id: number, payload: T): void {
    this.addRecordRoot({id, parentId: null, payload, children: [], level: 0, isLastChild: null, ignoreChildren: false});
  }

  private addRecordRoot(record: Record<T>): void {
    this.roots.push(record);
    this.records.set(record.id, record);
  }

  addChild(id: number, parentId: number, payload: T, ignoreChildren: boolean): void {
    const parent = this.records.get(parentId)!;
    const record = {id, parentId, payload, children: [], level: parent.level + 1, isLastChild: null, ignoreChildren};
    parent.children.push(record);
    this.records.set(record.id, record);
  }

  asList(): T[] {
    return this.flatRecords().map(record => record.payload);
  }

  asTreeItems(): [number, T, boolean][] {
    return this.flatRecords()
      .map(record => [record.level, record.payload, record.isLastChild!]);
  }

  private flatRecords(): Record<T>[] {
    return this.flattened(this.roots);
  }

  private flattened(records: Record<T>[]): Record<T>[] {
    const list: Record<T>[] = [];
    for (const [i, record] of records.entries()) {
      record.isLastChild = i === records.length - 1;
      list.push(record);
      if (!record.ignoreChildren) {
        const children = record.children;
        if (children.length > 0) {
          children.sort(this.recordSorter.bind(this));
          list.push(...this.flattened(children));
        }
      }
    }
    return list;
  }

  private recordSorter(a: Record<T>, b: Record<T>): number {
    return this.sorter(a.payload, b.payload);
  }
}
