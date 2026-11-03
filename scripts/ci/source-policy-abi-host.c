/* Host availability ONLY: no socket code, input, argv dispatch or policy change. */
#include "source-policy-abi-core.h"
long body(long *stack) {
    if(stack[0]!=1)return 90;
    state();
    number(call64(39,0,0,0,0,0,0));
    number(call32(20,0,0,0,0,0));
    number(call64(0x40000027,0,0,0,0,0,0));
    return 0;
}
