import * as React from 'react';

type TabsContextValue = {
    value: string;
    setValue: (v: string) => void;
    baseId: string;
};

const TabsContext = React.createContext<TabsContextValue | null>(null);

function cx(...cls: Array<string | false | null | undefined>) {
    return cls.filter(Boolean).join(' ');
}

type TabsProps = {
    defaultValue?: string;
    value?: string;
    onValueChange?: (v: string) => void;
    className?: string;
    children: React.ReactNode;
};

export function Tabs({
                         defaultValue,
                         value: valueProp,
                         onValueChange,
                         className,
                         children,
                     }: TabsProps) {
    const isControlled = valueProp !== undefined;
    const [internal, setInternal] = React.useState<string>(
        valueProp ?? defaultValue ?? ''
    );
    const baseId = React.useId();

    React.useEffect(() => {
        if (isControlled && valueProp !== undefined) setInternal(valueProp);
    }, [isControlled, valueProp]);

    const setValue = React.useCallback(
        (v: string) => {
            if (!isControlled) setInternal(v);
            onValueChange?.(v);
        },
        [isControlled, onValueChange]
    );

    const ctx: TabsContextValue = { value: internal, setValue, baseId };

    return (
        <TabsContext.Provider value={ctx}>
            <div className={className}>{children}</div>
        </TabsContext.Provider>
    );
}

type TabsListProps = {
    className?: string;
    children: React.ReactNode;
};

export function TabsList({ className, children }: TabsListProps) {
    return (
        <div role="tablist" className={cx('inline-flex items-center gap-1', className)}>
            {children}
        </div>
    );
}

type TabsTriggerProps = {
    value: string;
    className?: string;
    children: React.ReactNode;
};

export function TabsTrigger({ value, className, children }: TabsTriggerProps) {
    const ctx = React.useContext(TabsContext);
    if (!ctx) throw new Error('TabsTrigger must be used within <Tabs>');

    const active = ctx.value === value;
    const id = `${ctx.baseId}-tab-${value}`;
    const controls = `${ctx.baseId}-panel-${value}`;

    function onKeyDown(e: React.KeyboardEvent<HTMLButtonElement>) {
        if (!ctx) return;
        const container = (e.currentTarget.parentElement as HTMLElement) ?? null;
        if (!container) return;
        const triggers = Array.from(
            container.querySelectorAll<HTMLButtonElement>('[role="tab"]')
        );
        const idx = triggers.indexOf(e.currentTarget);
        if (idx === -1) return;

        if (e.key === 'ArrowRight') {
            e.preventDefault();
            triggers[(idx + 1) % triggers.length]?.focus();
        } else if (e.key === 'ArrowLeft') {
            e.preventDefault();
            triggers[(idx - 1 + triggers.length) % triggers.length]?.focus();
        }
    }

    return (
        <button
            id={id}
            role="tab"
            aria-selected={active}
            aria-controls={controls}
            type="button"
            onClick={() => ctx.setValue(value)}
            onKeyDown={onKeyDown}
            className={cx(
                'px-3 py-1.5 rounded-md text-sm border',
                active
                    ? 'bg-black text-white border-black'
                    : 'bg-transparent text-gray-700 hover:bg-gray-100 border-gray-300',
                className
            )}
        >
            {children}
        </button>
    );
}

type TabsContentProps = {
    value: string;
    className?: string;
    children: React.ReactNode;
};

export function TabsContent({ value, className, children }: TabsContentProps) {
    const ctx = React.useContext(TabsContext);
    if (!ctx) throw new Error('TabsContent must be used within <Tabs>');
    const active = ctx.value === value;
    const id = `${ctx.baseId}-panel-${value}`;
    const labelledBy = `${ctx.baseId}-tab-${value}`;

    return (
        <div
            id={id}
            role="tabpanel"
            aria-labelledby={labelledBy}
            hidden={!active}
            className={className}
        >
            {active ? children : null}
        </div>
    );
}

export default {
    Tabs,
    TabsList,
    TabsTrigger,
    TabsContent,
};
