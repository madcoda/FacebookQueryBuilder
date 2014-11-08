<?php namespace SammyK\FacebookQueryBuilder;

class GraphEdge extends GraphNode
{
    /**
     * Convert the nested query into an array of endpoints.
     *
     * @return array
     */
    public function toEndpoints()
    {
        $endpoints = [];

        $children = $this->getChildEdges();
        foreach ($children as $child)
        {
            $endpoints[] = '/' . implode('/', $child);
        }

        return $endpoints;
    }

    /**
     * Arrange the child edge nodes into a multidimensional array.
     *
     * @return array
     */
    public function getChildEdges()
    {
        $edges = [];
        $has_children = false;

        foreach ($this->fields as $v)
        {
            if ($v instanceof GraphEdge)
            {
                $has_children = true;

                $children = $v->getChildEdges();
                foreach ($children as $child_edges)
                {
                    $edges[] = array_merge([$this->name], $child_edges);
                }
            }
        }

        // Means this is the final node (no further sub edges)
        if ( ! $has_children)
        {
            $edges[] = [$this->name];
        }

        return $edges;
    }

    /**
     * Compile the modifier values.
     */
    public function compileModifiers()
    {
        if (count($this->modifiers) === 0) return;

        $processed_modifiers = [];

        foreach ($this->modifiers as $k => $v)
        {
            $processed_modifiers[] = urlencode($k) . '(' . urlencode($v) . ')';
        }

        $this->compiled_values[] = '.' . implode('.', $processed_modifiers);
    }

    /**
     * Compile the field values.
     */
    public function compileFields()
    {
        if (count($this->fields) === 0) return;

        $processed_fields = [];

        foreach ($this->fields as $v)
        {
            $processed_fields[] = $v instanceof GraphEdge ? $v->asUrl() : urlencode($v);
        }

        $this->compiled_values[] = '{' . implode(',',$processed_fields) . '}';
    }

    /**
     * Compile the the full URL.
     *
     * @return string
     */
    public function compileUrl()
    {
        $append = '';
        if (count($this->compiled_values) > 0)
        {
            $append = implode('', $this->compiled_values);
        }
        return $this->name . $append;
    }
}
