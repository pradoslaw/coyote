export class MissingParentError extends Error {
}

export class TreeMap<K, V> {
  private nodes = new Map<K, TreeMapNode<V>>();

  put(key: K, value: V, parentKey?: K): void {
    const newNode = {value, children: []};
    this.nodes.set(key, newNode);
    if (parentKey) {
      this.findNode(parentKey).children.push(newNode);
    }
  }

  private findNode(nodeKey: K): TreeMapNode<V> {
    const node = this.nodes.get(nodeKey);
    if (node) {
      return node;
    }
    throw new MissingParentError();
  }

  childrenOf(key: K): V[] {
    const node = this.nodes.get(key);
    if (!node) {
      return [];
    }
    return [
      node.value,
      ...node.children.flatMap(child => [child.value, ...child.children.map(x => x.value)]),
    ];
  }
}

interface TreeMapNode<V> {
  value: V;
  children: TreeMapNode<V>[];
}
