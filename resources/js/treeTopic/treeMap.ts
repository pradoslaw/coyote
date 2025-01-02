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

    function itemWithChildren(node: TreeMapNode<V>): V[] {
      return [node.value, ...node.children.flatMap(itemWithChildren)];
    }

    return node.children.flatMap(itemWithChildren);
  }
}

interface TreeMapNode<V> {
  value: V;
  children: TreeMapNode<V>[];
}
