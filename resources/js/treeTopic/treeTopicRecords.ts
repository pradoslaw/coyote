interface Record<T> {
  id: number;
  parentId: number | null;
  payload: T;
  children: Record<T>[];
  level: number;
  isLastChild: boolean | null;
  ignoreChildren: boolean;
}

export interface TreeItem<T> {
  item: T;
  nestLevel: number;
  hasNextSibling: boolean;
}

export class MultipleRootsError extends Error {
}

export class TreeTopicRecords<T> {
  private root?: Record<T>;
  private records: Map<number, Record<T>> = new Map<number, Record<T>>();

  constructor(private sorter: (a: T, b: T) => number) {
  }

  setRoot(id: number, payload: T): void {
    const root = this.rootRecord(id, payload);
    if (this.root) {
      throw new MultipleRootsError();
    }
    this.root = root;
    this.addRecord(root);
  }

  addChild(id: number, parentId: number, payload: T, ignoreChildren: boolean): void {
    const parent = this.records.get(parentId)!;
    const child = this.childRecord(id, parent, payload, ignoreChildren);
    parent.children.push(child);
    this.addRecord(child);
  }

  private addRecord(record: Record<T>): void {
    this.records.set(record.id, record);
  }

  flatTreeItems(): TreeItem<T>[] {
    if (this.root) {
      return this.flatTreeItemsChildrenOf(this.root.id);
    }
    return [];
  }

  flatTreeItemsChildrenOf(id: number): TreeItem<T>[] {
    return this.flattened([this.records.get(id)!]).map(this.recordToTreeItem);
  }

  private recordToTreeItem(record: Record<T>): TreeItem<T> {
    return {
      item: record.payload,
      nestLevel: record.level,
      hasNextSibling: !record.isLastChild,
    };
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

  private rootRecord(id: number, payload: T): Record<T> {
    return {id, parentId: null, payload, children: [], level: 0, isLastChild: null, ignoreChildren: false};
  }

  private childRecord(id: number, parent: Record<T>, payload: T, ignoreChildren: boolean): Record<T> {
    return {id, parentId: parent.id, payload, children: [], level: parent.level + 1, isLastChild: null, ignoreChildren};
  }
}
