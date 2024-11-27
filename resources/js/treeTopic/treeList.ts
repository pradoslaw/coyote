interface Record<T> {
  id: number;
  parentId: number | null;
  payload: T;
  children: Record<T>[];
  level: number;
}

export class TreeList<T> {
  private roots: Record<T>[] = [];
  private records: Map<number, Record<T>> = new Map<number, Record<T>>();

  constructor(private sorter: (a: T, b: T) => number) {
  }

  add(id: number, payload: T): void {
    this.addRecordRoot({id, parentId: null, payload, children: [], level: 0});
  }

  private addRecordRoot(record: Record<T>): void {
    this.roots.push(record);
    this.records.set(record.id, record);
  }

  addChild(id: number, parentId: number, payload: T): void {
    const parent = this.records.get(parentId)!;
    const record = {id, parentId, payload, children: [], level: parent.level + 1};
    parent.children.push(record);
    this.records.set(record.id, record);
  }

  asList(): T[] {
    return this.flatRecords().map(record => record.payload);
  }

  asIndentList(): [number, T][] {
    return this.flatRecords().map(record => [record.level, record.payload]);
  }

  private flatRecords(): Record<T>[] {
    return this.flattened(this.roots);
  }

  private flattened(records: Record<T>[]): Record<T>[] {
    const list: Record<T>[] = [];
    for (const record of records) {
      list.push(record);
      if (record.children.length > 0) {
        record.children.sort(this.recordSorter.bind(this));
        list.push(...this.flattened(record.children));
      }
    }
    return list;
  }

  private recordSorter(a: Record<T>, b: Record<T>): number {
    return this.sorter(a.payload, b.payload);
  }
}
